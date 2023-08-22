<?php

namespace DataMigrationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataMigrationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ars.command.data_checker')) {
            return;
        }

        $definition = $container->findDefinition('ars.command.data_migration');
        $taggedServices = $container->findTaggedServiceIds('data_migration');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addDataMigration', [new Reference($id)]);
        }
    }
}
