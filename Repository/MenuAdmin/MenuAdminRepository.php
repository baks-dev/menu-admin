<?php

namespace BaksDev\Menu\Admin\Repository\MenuAdmin;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Menu\Admin\Entity as EntityMenuAdmin;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MenuAdminRepository implements MenuAdminRepositoryInterface
{
    private Connection $connection;

    private TranslatorInterface $translator;

    public function __construct(Connection $connection, TranslatorInterface $translator)
    {
        $this->connection = $connection;
        $this->translator = $translator;
    }

    /**
     * Метод возвращает массив меню администратора с группировкой.
     */
    public function fetchAllAssociativeIndexed(): array
    {
        $qb = $this->connection->createQueryBuilder();

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

        $cacheFilesystem = new FilesystemAdapter('MenuAdmin');

        $config = $this->connection->getConfiguration();
        $config?->setResultCache($cacheFilesystem);
        
        return $this->connection->executeCacheQuery(
            $qb->getSQL(),
            $qb->getParameters(),
            $qb->getParameterTypes(),
            new QueryCacheProfile((60 * 60 * 365), MenuAdminIdentificator::TYPE.$locale)
        )->fetchAllAssociativeIndexed();
    }
}
