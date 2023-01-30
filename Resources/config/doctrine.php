<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Menu\Admin\Type\Event\MenuAdminEventType;
use BaksDev\Menu\Admin\Type\Event\MenuAdminEventUid;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use BaksDev\Menu\Admin\Type\Id\MenuAdminType;
use BaksDev\Menu\Admin\Type\Path\MenuAdminSectionPathType;
use BaksDev\Menu\Admin\Type\Path\MenuAdminSectionPathUid;
use BaksDev\Menu\Admin\Type\Section\MenuAdminSectionType;
use BaksDev\Menu\Admin\Type\Section\MenuAdminSectionUid;
use BaksDev\Menu\Admin\Type\SectionGroup\MenuAdminSectionGroup;
use BaksDev\Menu\Admin\Type\SectionGroup\MenuAdminSectionGroupType;

use Symfony\Config\DoctrineConfig;

return static function(DoctrineConfig $doctrine) {
	
	$doctrine->dbal()->type(MenuAdminIdentificator::TYPE)->class(MenuAdminType::class);
	$doctrine->dbal()->type(MenuAdminEventUid::TYPE)->class(MenuAdminEventType::class);
	$doctrine->dbal()->type(MenuAdminSectionUid::TYPE)->class(MenuAdminSectionType::class);
	$doctrine->dbal()->type(MenuAdminSectionPathUid::TYPE)->class(MenuAdminSectionPathType::class);
	$doctrine->dbal()->type(MenuAdminSectionGroup::TYPE)->class(MenuAdminSectionGroupType::class);
	
	$emDefault = $doctrine->orm()->entityManager('default');
	
	$emDefault->autoMapping(true);
	$emDefault->mapping('MenuAdmin')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Menu\Admin\Entity')
		->alias('MenuAdmin')
	;
};