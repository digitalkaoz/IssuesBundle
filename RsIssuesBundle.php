<?php

namespace Rs\IssuesBundle;

use Rs\Issues\Console\SearchCommand;
use Symfony\Component\Console\Application;
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
}
