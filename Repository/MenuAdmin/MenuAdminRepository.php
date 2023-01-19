<?php

namespace BaksDev\Menu\Admin\Repository\MenuAdmin;

use BaksDev\Menu\Admin\Entity as EntityMenuAdmin;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MenuAdminRepository implements MenuAdminRepositoryInterface
{
	private Connection $connection;
	
	private Locale $locale;
	
	public function __construct(Connection $connection, TranslatorInterface $translator)
	{
		$this->connection = $connection;
		$this->locale = new Locale($translator->getLocale());
	}
	
	public function fetchAllAssociativeIndexed() : array
	{
		$qb = $this->connection->createQueryBuilder();
		
		$qb->select('section.groups');
		$qb->addSelect('section_trans.name')->addGroupBy('section_trans.name');
		
		
		$qb->addSelect('ARRAY_TO_JSON(ARRAY_AGG(ARRAY[path.role, path.path, path_trans.name])) AS path');
		
		$qb->from(EntityMenuAdmin\MenuAdmin::TABLE, 'menu');
		
		$qb->join('menu',
			EntityMenuAdmin\Section\MenuAdminSection::TABLE,
			'section',
			'section.event = menu.event'
		);
		
		$qb->join(
			'section',
			EntityMenuAdmin\Section\Trans\MenuAdminSectionTrans::TABLE,
			'section_trans',
			'section_trans.section = section.id AND section_trans.local = :locale'
		);
		
		$qb->join(
			'section',
			EntityMenuAdmin\Section\Path\MenuAdminSectionPath::TABLE,
			'path',
			'path.section = section.id'
		);
		
		$qb->join(
			'path',
			EntityMenuAdmin\Section\Path\Trans\MenuAdminSectionPathTrans::TABLE,
			'path_trans',
			'path_trans.path = path.id AND path_trans.local = :locale'
		);
		
		$qb->addGroupBy('section.groups');
		
		$qb->where('menu.id = :menu');
		
		$qb->setParameter('menu', MenuAdminIdentificator::TYPE);
		$qb->setParameter('locale', $this->locale, Locale::TYPE);
		
		
//		dd(json_decode($qb->fetchAllAssociativeIndexed()['settings']['agg_path_trans']));
//
//		$arrAgg = $qb->fetchAllAssociative()[0]['agg_path'];
//		dd(json_decode($arrAgg));
//
//		dd($qb->fetchAllAssociative()[0]['agg_path']);
		
		
		return $qb->fetchAllAssociativeIndexed();
	}
	
	
	public function getPath() : array
	{
		return [];
	}
}