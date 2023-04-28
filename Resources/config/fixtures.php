<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Menu\Admin\DataFixtures\Menu\MenuAdminFixtures;
use BaksDev\Users\Groups\Group\DataFixtures\Security\RoleFixtures;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;

    $namespace = 'BaksDev\Menu\Admin';

    $services->load($namespace.'\DataFixtures\\', __DIR__.'/../../DataFixtures')
        ->exclude([
            __DIR__.'/../../DataFixtures/**/*DTO.php',
            __DIR__.'/../../DataFixtures/**/*translate.php',
        ])
    ;

    $services->set(MenuAdminFixtures::class)
        ->arg('$menu', tagged_iterator('baks.menu.admin'))
    ;
};
