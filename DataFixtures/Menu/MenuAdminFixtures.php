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

namespace BaksDev\Menu\Admin\DataFixtures\Menu;

use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin\ActiveMenuAdminEventRepositoryInterface;
use BaksDev\Menu\Admin\Repository\ExistPath\MenuAdminExistPathRepositoryInterface;
use BaksDev\Users\Groups\Role\Type\RolePrefix\RolePrefix;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MenuAdminFixtures extends Fixture
{
    private iterable $menu;

    private MenuAdminFixturesHandler $handler;

    private TranslatorInterface $translator;

    private MenuAdminExistPathRepositoryInterface $MenuAdminPath;

    private ActiveMenuAdminEventRepositoryInterface $activeMenuAdminEvent;

    public function __construct(
        iterable $menu,
        MenuAdminFixturesHandler $handler,
        TranslatorInterface $translator,
        MenuAdminExistPathRepositoryInterface $MenuAdminPath,
        ActiveMenuAdminEventRepositoryInterface $activeMenuAdminEvent,
    ) {
        $this->handler = $handler;
        $this->menu = $menu;
        $this->translator = $translator;
        $this->MenuAdminPath = $MenuAdminPath;
        $this->activeMenuAdminEvent = $activeMenuAdminEvent;
    }

    /** Добавляем в пункты меню Администрирования разделы */
    public function load(ObjectManager $manager): void
    {
        // php bin/console doctrine:fixtures:load --append

        // Сбрасываем кеш меню
        $cache = new FilesystemAdapter('MenuAdmin');
        $cache->clear();

        /** @var MenuAdminFixturesInterface $menu */
        foreach ($this->menu as $menu)
        {


            // Если не указана секция меню - пропускаем
            if ($menu->getGroupMenu() === false)
            {
                continue;
            }

            // Если пункт меню уже добавлен - пропускаем
            if ($this->MenuAdminPath->isExist($menu->getPath()))
            {
                continue;
            }

            $Event = $this->activeMenuAdminEvent->getEventOrNullResult();

            if (!$Event)
            {
                return;
            }

            $MenuAdminDTO = new MenuAdminPath\MenuAdminDTO();
            $Event->getDto($MenuAdminDTO);

            /** @var MenuAdminPath\Section\MenuAdminSectionDTO $MenuAdminSectionDTO */
            foreach ($MenuAdminDTO->getSection() as $MenuAdminSectionDTO)
            {
                if ($menu->getGroupMenu() === $MenuAdminSectionDTO->getGroup()->getType())
                {
                    $MenuAdminSectionPathDTO = new MenuAdminPath\Section\Path\MenuAdminSectionPathDTO();
                    $MenuAdminSectionPathDTO->setRole(new RolePrefix($menu->getRole()));
                    $MenuAdminSectionPathDTO->setPath($menu->getPath());
                    $MenuAdminSectionPathDTO->setSort($menu->getSortMenu());
                    $MenuAdminSectionPathDTO->setDropdown($menu->getDropdownMenu());
                    $MenuAdminSectionPathDTO->setModal($menu->getModal());
                    $MenuAdminSectionDTO->addPath($MenuAdminSectionPathDTO);

                    // Настройки локали пункта меню
                    $MenuAdminSectionPathTrans = $MenuAdminSectionPathDTO->getTranslate();

                    /** @var MenuAdminPath\Section\Path\Trans\MenuAdminSectionPathTransDTO $MenuAdminSectionPathTransDTO */
                    foreach ($MenuAdminSectionPathTrans as $MenuAdminSectionPathTransDTO)
                    {
                        $locale = $MenuAdminSectionPathTransDTO->getLocal()->getValue();

                        // Название пункта меню
                        $MenuName = $this->translator->trans(id: $menu->getRole().'.name', domain: 'security', locale: $locale);
                        $MenuAdminSectionPathTransDTO->setName($MenuName);

                        if ($MenuName === $menu->getRole().'.name')
                        {
                            throw new InvalidArgumentException(
                                sprintf(
                                    'Для префикса роли %s не добавлено название в файл переводов домена security локали %s',
                                    $menu->getRole(),
                                    $locale
                                )
                            );
                        }

                        // Опсиание пункта меню
                        $MenuDesc = $this->translator->trans(id: $menu->getRole().'.desc', domain: 'security', locale: $locale);
                        $MenuAdminSectionPathTransDTO->setDescription($MenuDesc);

                        if ($MenuDesc === $menu->getRole().'.desc')
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

            if (!$MenuAdmin instanceof MenuAdmin)
            {
                throw new InvalidArgumentException(
                    sprintf('Ошибка %s при обновлении пункта меню', $MenuAdmin)
                );
            }
        }

    }

    public function getDependencies(): array
    {
        return [
            MenuAdminSectionFixtures::class,
        ];
    }
}
