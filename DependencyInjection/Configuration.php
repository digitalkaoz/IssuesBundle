<?php

namespace Rs\IssuesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rs_issues');

        $rootNode
            ->children()
                ->append($this->buildTrackerConfig('github'))
                ->append($this->buildTrackerConfig('jira'))
                //update in sync with trackers implemented in "issues" itself
            ->end();

        return $treeBuilder;
    }

    /**
     * build a tracker config
     *
     * @param  string              $name
     * @return NodeParentInterface
     */
    private function buildTrackerConfig($name)
    {
        return (new TreeBuilder())
            ->root($name)
                ->prototype('scalar')
            ->end()
        ;
    }
}
