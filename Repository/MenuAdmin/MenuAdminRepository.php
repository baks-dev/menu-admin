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
 *
 */

namespace BaksDev\Menu\Admin\Repository\MenuAdmin;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Entity\Section\MenuAdminSection;
use BaksDev\Menu\Admin\Entity\Section\Path\Key\MenuAdminSectionPathKey;
use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPath;
use BaksDev\Menu\Admin\Entity\Section\Path\Trans\MenuAdminSectionPathTrans;
use BaksDev\Menu\Admin\Entity\Section\Trans\MenuAdminSectionTrans;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use BaksDev\Menu\Admin\Type\Section\MenuAdminSectionUid;

/** @see MenuAdminResult */
final class MenuAdminRepository implements MenuAdminInterface
{
    private MenuAdminSectionUid|false $sectionId = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    /**
     * Фильтр по идентификатору секции
     */
    public function onSectionId(MenuAdminSection|MenuAdminSectionUid|string $sectionId): self
    {
        if(empty($sectionId))
        {
            $this->sectionId = false;
            return $this;
        }

        if(is_string($sectionId))
        {
            $sectionId = new MenuAdminSectionUid($sectionId);
        }

        if($sectionId instanceof MenuAdminSection)
        {
            $sectionId = $sectionId->getId();
        }

        $this->sectionId = $sectionId;
        return $this;
    }

    /**
     * Метод возвращает массив MenuAdminResult меню администратора с группировкой.
     *
     * @return array<string, MenuAdminResult>|array<empty>
     */
    public function findAll(): array
    {
        $builder = $this->builder();
        $builder->enableCache('menu-admin', '1 day');

        return $builder->fetchAllIndexHydrate(MenuAdminResult::class);
    }

    /**
     * Метод возвращает массив меню администратора с группировкой.
     */
    public function fetchAllAssociativeIndexed(): array
    {
        $builder = $this->builder();
        $builder->enableCache('menu-admin', '1 day');

        return $builder->fetchAllAssociativeIndexed();
    }

    private function builder(): DBALQueryBuilder
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(MenuAdmin::class, 'menu')
            ->where('menu.id = :menu')
            ->setParameter('menu', MenuAdminIdentificator::TYPE);

        $dbal
            ->addSelect('section.groups')
            ->addSelect('section.id AS section_id')
            ->addSelect('section.sort')
            ->addSelect('section_trans.name');

        /** Если передан фильтр по идентификатору секции */
        if($this->sectionId instanceof MenuAdminSectionUid)
        {
            $dbal
                ->join(
                    'menu',
                    MenuAdminSection::class,
                    'section',
                    '
                        section.event = menu.event AND 
                        section.id = :section'
                )
                ->setParameter('section', $this->sectionId, MenuAdminSectionUid::TYPE);
        }
        else
        {
            $dbal
                ->join(
                    'menu',
                    MenuAdminSection::class,
                    'section',
                    'section.event = menu.event'
                );
        }


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
            MenuAdminSectionPathKey::class,
            'path_key',
            'path_key.path = path.id',
        );

        $dbal->addSelect(
            "JSON_AGG
			( 
		
					JSONB_BUILD_OBJECT
					(
						'0', path.sort,
						'role', path.role,
						'href', path.path,
						'key', path_key.value,
						'name', path_trans.name,
						'dropdown', path.dropdown,
						'modal', path.modal
					)
		
			)
			AS path",
        );

        $dbal->leftJoin(
            'path',
            MenuAdminSectionPathTrans::class,
            'path_trans',
            'path_trans.path = path.id AND path_trans.local = :local'
        );

        $dbal->allGroupByExclude();

        $dbal->orderBy('section.sort', 'ASC');

        return $dbal;
    }
}
