<?php
/*
 * This file is part of the FreshCentrifugoBundle.
 *
 * (c) Artem Henvald <genvaldartem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BaksDev\Menu\Admin;

use DirectoryIterator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class BaksDevMenuAdminBundle extends AbstractBundle
{
    public const NAMESPACE = __NAMESPACE__.'\\';

    public const PATH = __DIR__.DIRECTORY_SEPARATOR;

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('baks_menu_admin')
            ->defaultValue(true)
            ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('baks_menu_admin', true);
    }

    // The compiler pass
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition('twig')) {
            return;
        }

        dump(6546545);

        $exists = $container->getParameter('baks_menu_admin');
        $def = $container->getDefinition('twig');
        $def->addMethodCall('addGlobal', ['baks_menu_admin', $exists]);
    }

}
