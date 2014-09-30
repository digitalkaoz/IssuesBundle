<?php

namespace Rs\IssuesBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * SynchronizersPass
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class SynchronizersPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('rs_issues.command.sync')) {
            return;
        }

        $command = $container->getDefinition('rs_issues.command.sync');
        $serviceIds = $container->findTaggedServiceIds('rs_issues.synchronizer');

        foreach($serviceIds as $id => $serviceConfig) {
            $command->addMethodCall('addSynchronizer', array(new Reference($id)));
        }
    }
}