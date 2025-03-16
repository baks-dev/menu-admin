<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPath;
use BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin\ActiveMenuAdminEventInterface;
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
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'baks:menu-admin:path',
    description: 'Обновляет ссылки меню администратора',
    aliases: ['baks:project:upgrade:menu-admin:path']
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeAdminMenuPathCommand extends Command implements ProjectUpgradeInterface
{
    public function __construct(
        #[AutowireIterator('baks.menu.admin')] private readonly iterable $menu,
        private readonly MenuAdminHandler $handler,
        private readonly TranslatorInterface $translator,
        private readonly ActiveMenuAdminEventInterface $activeMenuAdminEvent,
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Обновляем ссылки меню администратора');

        /** Сбрасываем ссылки */
        $table = $this->DBALQueryBuilder->table(MenuAdminSectionPath::class);
        $this->DBALQueryBuilder
            ->prepare(sprintf('TRUNCATE TABLE %s CASCADE', $table))
            ->executeQuery();


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
                if($MenuAdminSectionDTO->getGroup() === false)
                {
                    continue;
                }

                if(is_array($menu->getGroupMenu()))
                {
                    foreach($menu->getGroupMenu() as $groupMenu)
                    {
                        if($groupMenu && $groupMenu::equals($MenuAdminSectionDTO->getGroup()->getTypeValue()))
                        {
                            $this->add($MenuAdminSectionDTO, $menu);
                        }
                    }

                    continue;
                }

                if($menu->getGroupMenu()::equals($MenuAdminSectionDTO->getGroup()->getTypeValue()))
                {
                    $this->add($MenuAdminSectionDTO, $menu);
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


    public function add(MenuAdminPathSectionDTO $MenuAdminSectionDTO, MenuAdminInterface $menu): void
    {
        $MenuAdminSectionPathDTO = new MenuAdminPathSectionPathDTO();
        $MenuAdminSectionPathDTO->setRole(new GroupRolePrefix($menu->getRole()));
        $MenuAdminSectionPathDTO->setPath($menu->getPath());
        $MenuAdminSectionPathDTO->setSort($menu::getSortMenu());
        $MenuAdminSectionPathDTO->setDropdown($menu->getDropdownMenu());
        $MenuAdminSectionPathDTO->setModal($menu->getModal());
        $MenuAdminSectionDTO->addPath($MenuAdminSectionPathDTO);

        $localId = $menu->getRole().($menu->getPath() ? '.name' : '.header');


        // Настройки локали пункта меню
        $MenuAdminSectionPathTrans = $MenuAdminSectionPathDTO->getTranslate();

        /** @var MenuAdminPathSectionPathTransDTO $MenuAdminSectionPathTransDTO */
        foreach($MenuAdminSectionPathTrans as $MenuAdminSectionPathTransDTO)
        {
            $locale = $MenuAdminSectionPathTransDTO->getLocal()->getLocalValue();

            // Название пункта меню
            $MenuName = $this->translator->trans(
                id: $localId,
                domain: 'security',
                locale: $locale
            );

            $MenuAdminSectionPathTransDTO->setName($MenuName);

            if($MenuName === $localId)
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


    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 99;
    }
}
