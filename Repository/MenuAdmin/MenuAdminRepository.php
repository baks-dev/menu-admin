<?php

namespace BaksDev\Menu\Admin\Repository\MenuAdmin;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Menu\Admin\Entity as EntityMenuAdmin;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MenuAdminRepository implements MenuAdminRepositoryInterface
{
    private TranslatorInterface $translator;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder,  TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает массив меню администратора с группировкой.
     */
    public function fetchAllAssociativeIndexed(): array
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        /** ЛОКАЛЬ */
        $locale = new Locale($this->translator->getLocale());
        $qb->setParameter('local', $locale, Locale::TYPE);

        $qb->addSelect('section.groups');
        $qb->addSelect('section.sort')->addGroupBy('section.sort');
        $qb->addSelect('section_trans.name')->addGroupBy('section_trans.name');


        $qb->addSelect(
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

        $qb->from(EntityMenuAdmin\MenuAdmin::TABLE, 'menu');

        $qb->join(
            'menu',
            EntityMenuAdmin\Section\MenuAdminSection::TABLE,
            'section',
            'section.event = menu.event'
        );

        $qb->leftJoin(
            'section',
            EntityMenuAdmin\Section\Trans\MenuAdminSectionTrans::TABLE,
            'section_trans',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        $qb->join(
            'section',
            EntityMenuAdmin\Section\Path\MenuAdminSectionPath::TABLE,
            'path',
            'path.section = section.id'
        );

        $qb->leftJoin(
            'path',
            EntityMenuAdmin\Section\Path\Trans\MenuAdminSectionPathTrans::TABLE,
            'path_trans',
            'path_trans.path = path.id AND path_trans.local = :local'
        );

        $qb->addGroupBy('section.groups');

        $qb->where('menu.id = :menu');

        $qb->setParameter('menu', MenuAdminIdentificator::TYPE);

        $qb->orderBy('section.sort', 'ASC');


        /* Кешируем результат DBAL */
        return $qb->enableCache('MenuAdmin', 3600)->fetchAllAssociativeIndexed();

    }
}
