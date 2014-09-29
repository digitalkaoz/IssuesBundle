<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Github\GithubTracker;
use Rs\Issues\Tracker;
use Rs\IssuesBundle\Storage\Storage;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class Synchronizer
{
    /**
     * @var array
     */
    private $repos = array();
    /**
     * @var Tracker
     */
    private $tracker;
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Tracker $tracker
     * @param Storage $storage
     * @param array   $repos
     */
    public function __construct(Tracker $tracker, Storage $storage, array $repos)
    {
        $this->repos = $repos;
        $this->tracker = $tracker;
        $this->storage = $storage;
    }

    public function synchronize()
    {
        $this->storage->cleanup();

        foreach ($this->repos as $repo) {
            if ($this->tracker instanceof GithubTracker && 1 !== preg_match('/^[a-zA-Z0-9\.-]+\/[a-zA-Z0-9\.-]+/', $repo)) {
                $this->expand($repo);
            } else {
                $this->fetch($repo);
            }
        }
    }

    private function fetch($repo)
    {
        $project = $this->tracker->getProject($repo);

        $this->storage->saveProject($project);

        foreach ($project->getIssues() as $issue) {
            $this->storage->saveIssue($issue, $project);
        }
    }

    private function expand($repo)
    {
        preg_match('/([a-zA-Z0-9\.-]+)\/.+/', $repo, $matches);

        $repos = $this->tracker->getClient()->api('user')->repositories($matches[1]);

        if (1 === preg_match('/([a-zA-Z0-9\.-])\/\*/', $repo, $matches)) {
            // org/*
            foreach ($repos as $repo) {
                $this->fetch($repo['full_name']);
            }
        } else {
            // org/[Foo|Bar]+
            // org/(!...)
            foreach ($repos as $key => $expandedRepo) {
                if (preg_match('/'.str_replace('/', '\/', $repo).'/', $expandedRepo['full_name'])) {
                    $this->fetch($expandedRepo['full_name']);
                }
            }
        }
    }
}
