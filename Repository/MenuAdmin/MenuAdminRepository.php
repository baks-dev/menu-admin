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

namespace BaksDev\Menu\Admin\Repository\MenuAdmin;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Entity\Section\MenuAdminSection;
use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPath;
use BaksDev\Menu\Admin\Entity\Section\Path\Trans\MenuAdminSectionPathTrans;
use BaksDev\Menu\Admin\Entity\Section\Trans\MenuAdminSectionTrans;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;

final readonly class MenuAdminRepository implements MenuAdminInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод возвращает массив меню администратора с группировкой.
     */
    public function fetchAllAssociativeIndexed(): array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->addSelect('section.groups')
            ->addSelect('section.sort')
            ->addSelect('section_trans.name');

        $dbal->addSelect(
            "JSON_AGG
			( 
		
					JSONB_BUILD_OBJECT
					(
						'0', path.sort,
						'role', path.role,
						'href', path.path,
						'name', path_trans.name,
						'dropdown', path.dropdown,
						'modal', path.modal
					)
		
			)
			AS path"
        );

        $dbal
            ->from(MenuAdmin::class, 'menu')
            ->where('menu.id = :menu')
            ->setParameter('menu', MenuAdminIdentificator::TYPE);

        $dbal->join(
            'menu',
            MenuAdminSection::class,
            'section',
            'section.event = menu.event'
        );

        $dbal->leftJoin(
            'section',
            MenuAdminSectionTrans::class,
            'section_trans',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        $dbal->join(
            'section',
            MenuAdminSectionPath::class,
            'path',
            'path.section = section.id'
        );

        $dbal->leftJoin(
            'path',
            MenuAdminSectionPathTrans::class,
            'path_trans',
            'path_trans.path = path.id AND path_trans.local = :local'
        );


        $dbal->allGroupByExclude();

        $dbal->orderBy('section.sort', 'ASC');

        /* Кешируем результат DBAL */
        return $dbal->enableCache('menu-admin', '1 day')->fetchAllAssociativeIndexed();
    }
}
