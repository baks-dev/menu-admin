<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes)
{

    /* Контроллер по умолчанию */
    $routes->import('../../Controller', 'annotation')
      ->prefix(\BaksDev\Core\Type\Locale\Locale::routes())
      ->namePrefix('MenuAdmin:');
    
};