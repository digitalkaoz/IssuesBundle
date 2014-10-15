<?php

namespace Rs\IssuesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RsIssuesExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        if ($container->hasParameter('rs_issues')) {
            $configs = array_merge($configs, array($container->getParameter('rs_issues')));
        }

        $this->loadFiles($container);
        $this->processConfig($container, $configs);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadFiles(ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('trackers.xml');
        $loader->load('synchronizers.xml');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $configs
     */
    private function processConfig(ContainerBuilder $container, array $configs)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config as $name => $repos) {
            if (!$container->hasDefinition('rs_issues.synchronizer.'.$name)) {
                continue;
            }

            $definition = $container->getDefinition('rs_issues.synchronizer.'.$name);
            $definition->addMethodCall('setRepos', array($repos));
            $definition->addTag('rs_issues.synchronizer');
        }
    }
}
