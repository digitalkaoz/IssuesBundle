<?php

namespace Rs\IssuesBundle\Tracker;

use Rs\Issues\Gitlab\GitlabTracker;
use Rs\Issues\Jira\JiraTracker;

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
     * @param string $token
     * @return GithubTracker
     */
    public function createGithubTracker($token)
    {
        $clazz = $this->githubClass;

        return new $clazz($token);
    }

    /**
     * @param string $host
     * @param string $token
     * @return GitlabTracker
     */
    public function createGitlabTracker($host, $token)
    {
        $clazz = $this->gitlabClass;

        return new $clazz($host, $token);
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @return JiraTracker
     */
    public function createJiraTracker($host, $username = null, $password = null)
    {
        $clazz = $this->jiraClass;

        return new $clazz($host, $username, $password);
    }
} 