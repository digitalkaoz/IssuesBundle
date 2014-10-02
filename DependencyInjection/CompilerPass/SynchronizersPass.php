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
        if (!$container->hasDefinition('rs_issues.command.sync') || !$container->hasDefinition('rs_issues.provider.api')) {
            return;
        }

        if ($container->hasDefinition('rs_issues.command.sync')) {
            $command = $container->getDefinition('rs_issues.command.sync');
        }

        if ($container->hasDefinition('rs_issues.provider.api')) {
            $provider = $container->getDefinition('rs_issues.provider.api');
        }

        $serviceIds = $container->findTaggedServiceIds('rs_issues.synchronizer');

        foreach ($serviceIds as $id => $serviceConfig) {
            if (isset($command)) {
                $command->addMethodCall('addSynchronizer', array(new Reference($id)));
            }
            if (isset($provider)) {
                $provider->addMethodCall('addSynchronizer', array(new Reference($id)));
            }
        }
    }
}
