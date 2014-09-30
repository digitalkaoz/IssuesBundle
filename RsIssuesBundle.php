<?php

namespace Rs\IssuesBundle;

use Rs\Issues\Console\SearchCommand;
use Rs\IssuesBundle\DependencyInjection\CompilerPass\StoragePass;
use Rs\IssuesBundle\DependencyInjection\CompilerPass\SynchronizersPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RsIssuesBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function registerCommands(Application $application)
    {
        parent::registerCommands($application);

        $application->add((new SearchCommand())->setName('issues:search'));
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new StoragePass());
        $container->addCompilerPass(new SynchronizersPass());
    }
}
