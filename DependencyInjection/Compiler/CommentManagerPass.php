<?php
namespace Mykees\CommentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class PlaceLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // get the msb.places.chained_locator service definition
        $definition = $container->findDefinition('mykees.comment.manager');

        // for every service tagged place_locator...
        foreach ($container->findTaggedServiceIds('mykees_manager') as $id => $tags) {
            // ... add it as a call to addLocator of the msb.places.chained_locator service definition
            $definition->addMethodCall('addLocator', [new Reference($id)]);
        }
    }
}