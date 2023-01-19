<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Menu\Admin\Twig\MenuAdminExtension;
use Symfony\Config\TwigConfig;

return static function (ContainerConfigurator $configurator, TwigConfig $config)
{
	$services = $configurator->services()
		->defaults()
		->autowire()
		->autoconfigure()
	;
	

	$config->path(__DIR__.'/../view', 'MenuAdmin');
	
	/** Twig Extension */
	
	$services->set('menu.admin.twig.extension')
		->class(MenuAdminExtension::class)
		->tag('twig.extension');
	
};






