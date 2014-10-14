<?php

namespace Rs\IssuesBundle\Tracker;

use Rs\Issues\Tracker;

/**
 * TrackerFactory
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class TrackerFactory
{
    private $githubClass;
    private $jiraClass;
    private $gitlabClass;

    public function __construct($githubClass, $jiraClass, $gitlabClass)
    {
        $this->githubClass = $githubClass;
        $this->jiraClass = $jiraClass;
        $this->gitlabClass = $gitlabClass;
    }

    /**
     * @param  string  $token
     * @return Tracker
     */
    public function createGithubTracker($token = null)
    {
        $clazz = $this->githubClass;

        return new $clazz($token);
    }

    /**
     * @param  string  $host
     * @param  string  $token
     * @return Tracker
     */
    public function createGitlabTracker($host, $token)
    {
        $clazz = $this->gitlabClass;

        return new $clazz($host, $token);
    }

    /**
     * @param  string  $host
     * @param  string  $username
     * @param  string  $password
     * @return Tracker
     */
    public function createJiraTracker($host, $username = null, $password = null)
    {
        $clazz = $this->jiraClass;

        return new $clazz($host, $username, $password);
    }
}
