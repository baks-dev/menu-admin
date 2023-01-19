<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


return static function (ContainerConfigurator $configurator)
{

    $services = $configurator->services()
      ->defaults()
      ->autowire()      // Automatically injects dependencies in your services.
      ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;
    
	$namespace = 'BaksDev\Menu\Admin';
	
	$services->load($namespace.'\Controller\\', __DIR__.'/../../Controller')
		->tag('controller.service_arguments');

    $services->load($namespace.'\Repository\\', __DIR__.'/../../Repository');
    
    //$services->load($namespace.'\UseCase\\', __DIR__.'/../../UseCase')
    //  ->exclude('../../UseCase/**/*DTO.php');
    
    $services->load($namespace.'\DataFixtures\\', __DIR__.'/../../DataFixtures')
      ->exclude(__DIR__.'/../../DataFixtures/**/*DTO.php');


};
