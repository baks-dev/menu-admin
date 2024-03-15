<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Menu\Admin\Command\Upgrade;

use BaksDev\Core\Command\Update\ProjectUpgradeInterface;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin\ActiveMenuAdminEventRepositoryInterface;
use BaksDev\Menu\Admin\Repository\ExistPath\MenuAdminExistPathRepositoryInterface;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminHandler;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\MenuAdminPathDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\MenuAdminPathSectionDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\Path\MenuAdminPathSectionPathDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\Path\Trans\MenuAdminPathSectionPathTransDTO;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'baks:menu-admin:path',
    description: 'Обновляет ссылки меню администратора',
    aliases: ['baks:project:upgrade:menu-admin:path']
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeAdminMenuPathCommand extends Command implements ProjectUpgradeInterface
{
    private iterable $menu;

    private MenuAdminHandler $handler;

    private TranslatorInterface $translator;

    private MenuAdminExistPathRepositoryInterface $MenuAdminPath;

    private ActiveMenuAdminEventRepositoryInterface $activeMenuAdminEvent;

    public function __construct(
        #[TaggedIterator('baks.menu.admin')] iterable $menu,
        MenuAdminHandler $handler,
        TranslatorInterface $translator,
        MenuAdminExistPathRepositoryInterface $MenuAdminPath,
        ActiveMenuAdminEventRepositoryInterface $activeMenuAdminEvent,
    )
    {
        parent::__construct();

        $this->menu = $menu;
        $this->handler = $handler;
        $this->translator = $translator;
        $this->MenuAdminPath = $MenuAdminPath;
        $this->activeMenuAdminEvent = $activeMenuAdminEvent;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Обновляем ссылки меню администратора');

        /** @var MenuAdminInterface $menu */
        foreach($this->menu as $menu)
        {
            // Если не указана секция меню - пропускаем
            if($menu->getGroupMenu() === false)
            {
                continue;
            }

            // Если пункт меню уже добавлен - пропускаем
//            if($this->MenuAdminPath->isExist($menu->getPath()))
//            {
//                continue;
//            }

            $Event = $this->activeMenuAdminEvent->getEventOrNullResult();


            if(!$Event)
            {
                return Command::SUCCESS;
            }

            $MenuAdminDTO = new MenuAdminPathDTO();
            $Event->getDto($MenuAdminDTO);

            /** @var MenuAdminPathSectionDTO $MenuAdminSectionDTO */
            foreach($MenuAdminDTO->getSection() as $MenuAdminSectionDTO)
            {
                if($menu->getGroupMenu()::equals($MenuAdminSectionDTO->getGroup()->getTypeValue()))
                {

                    $MenuAdminSectionPathDTO = new MenuAdminPathSectionPathDTO();
                    $MenuAdminSectionPathDTO->setRole(new GroupRolePrefix($menu->getRole()));
                    $MenuAdminSectionPathDTO->setPath($menu->getPath());
                    $MenuAdminSectionPathDTO->setSort($menu->getSortMenu());
                    $MenuAdminSectionPathDTO->setDropdown($menu->getDropdownMenu());
                    $MenuAdminSectionPathDTO->setModal($menu->getModal());
                    $MenuAdminSectionDTO->addPath($MenuAdminSectionPathDTO);

                    // Настройки локали пункта меню
                    $MenuAdminSectionPathTrans = $MenuAdminSectionPathDTO->getTranslate();

                    /** @var MenuAdminPathSectionPathTransDTO $MenuAdminSectionPathTransDTO */
                    foreach($MenuAdminSectionPathTrans as $MenuAdminSectionPathTransDTO)
                    {
                        $locale = $MenuAdminSectionPathTransDTO->getLocal()->getLocalValue();

                        // Название пункта меню
                        $MenuName = $this->translator->trans(
                            id: $menu->getRole().'.name',
                            domain: 'security',
                            locale: $locale
                        );

                        $MenuAdminSectionPathTransDTO->setName($MenuName);

                        if($MenuName === $menu->getRole().'.name')
                        {
                            throw new InvalidArgumentException(
                                sprintf(
                                    'Для префикса роли %s не добавлено название в файл переводов домена security локали %s',
                                    $menu->getRole(),
                                    $locale
                                )
                            );
                        }

                        // Описание пункта меню
                        $MenuDesc = $this->translator->trans(id: $menu->getRole().'.desc', domain: 'security', locale: $locale);
                        $MenuAdminSectionPathTransDTO->setDescription($MenuDesc);

                        if($MenuDesc === $menu->getRole().'.desc')
                        {
                            throw new InvalidArgumentException(
                                sprintf(
                                    'Для префикса роли %s не добавлено краткое описание в файл переводов домена security локали %s',
                                    $menu->getRole(),
                                    $locale
                                )
                            );
                        }
                    }
                }

            }

            $MenuAdmin = $this->handler->handle($MenuAdminDTO);

            if(!$MenuAdmin instanceof MenuAdmin)
            {
                throw new InvalidArgumentException(
                    sprintf('Ошибка %s при обновлении пункта меню администратора', $MenuAdmin)
                );
            }
        }


        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 99;
    }
}
