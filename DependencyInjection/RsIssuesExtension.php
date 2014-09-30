<?php

namespace Rs\IssuesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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

        $this->loadFiles($container);
        $this->processConfig($container, array($container->getParameter('rs_issues')));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadFiles(ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $configs
     */
    private function processConfig(ContainerBuilder $container, array $configs)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config as $name => $repos) {
            $definition = $container->register('rs_issues.synchronizer.'.$name, $container->getParameter('rs_issues.synchronizer.'.$name.'.class'));

            $definition->setArguments(array(
                new Reference('rs_issues.tracker.'.$name),
                new Reference('rs_issues.storage'),
                $repos
            ));

            $definition->addTag('rs_issues.synchronizer');
        }
    }
}
