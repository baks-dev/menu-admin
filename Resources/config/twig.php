<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\TwigConfig;

return static function(TwigConfig $twig) {
    $twig->path(__DIR__.'/../view', 'MenuAdmin');
};






