<?php
/**
 * dashboard
 */

namespace Rs\IssuesBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * StoragePass
 * @author Robert Schönthal <robert.schoenthal@sinnerschrader.com> 
 */
class StoragePass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasAlias('rs_issues.storage') || !$container->hasDefinition('rs_issues.storage.es')) {
            return;
        }

        //defaults to Elasticsearch Storage
        $container->setAlias('rs_issues.storage', 'rs_issues.storage.es');
    }
}