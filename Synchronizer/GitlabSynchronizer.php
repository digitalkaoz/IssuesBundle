<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Tracker;
use Rs\IssuesBundle\Tracker\GitlabTracker;
use Rs\IssuesBundle\Storage\Storage;
use Rs\IssuesBundle\Tracker\TrackerFactory;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class GitlabSynchronizer implements Synchronizer
{
    /**
     * @var array
     */
    private $repos = array();
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var TrackerFactory
     */
    private $trackerFactory;

    /**
     * @param Storage $storage
     * @param TrackerFactory $trackerFactory
     */
    public function __construct(Storage $storage, TrackerFactory $trackerFactory)
    {
        $this->storage = $storage;
        $this->trackerFactory = $trackerFactory;
    }

    /**
     * @inheritdoc
     */
    public function synchronize($cb = null)
    {
        foreach ($this->repos as $repo) {
            list($host, $repository, $token) = explode(' ', $repo);
            $tracker = $this->trackerFactory->createGitlabTracker($host, $token);

            if (1 !== preg_match('/^[a-zA-Z0-9\.-]+\/[a-zA-Z0-9\.-]+/', $repository)) {
                $this->expand($repository, $tracker, $cb);
            } else {
                $this->fetch($repository, $tracker, $cb);
            }
        }
    }

    /**
     * @param string $repo
     * @param Tracker $tracker
     * @param \Closure $cb
     */
    private function fetch($repo, Tracker $tracker, $cb =null)
    {
        try {
            $project = $tracker->getProject($repo);
            $this->storage->saveProject($project);

            if (is_callable($cb)) {
                $cb(sprintf('synchronized "%s"', $repo));
            }
        } catch (\Exception $e) {
            if (is_callable($cb)) {
                $cb(sprintf('failed !%s! with %s', $repo, $e->getMessage()));
            }
        }
    }

    private function expand($repo, Tracker $tracker, $cb = null)
    {
        preg_match('/([a-zA-Z0-9\.-]+)\/.+/', $repo, $matches);

        $repos = $tracker->getClient()->api('projects')->accessible(1, 9999);

        if (1 === preg_match('/([a-zA-Z0-9\.-])\/\*/', $repo, $matches)) {
            // org/*
            foreach ($repos as $_repo) {
                $ns = explode('/', $repo);
                $ns = $ns[0];
                if (1 === preg_match('/'.$ns.'\/(.)*/', $_repo['path_with_namespace'])) {
                    $this->fetch($_repo['path_with_namespace'], $tracker, $cb);
                }
            }
        } else {
            // org/[Foo|Bar]+
            // org/(!...)
            foreach ($repos as $key => $expandedRepo) {
                if (preg_match('/'.str_replace('/', '\/', $repo).'/', $expandedRepo['path_with_namespace'])) {
                    $this->fetch($expandedRepo['path_with_namespace'], $tracker, $cb);
                }
            }
        }
    }

    /**
     * @param array $repos
     */
    public function setRepos(array $repos)
    {
        $this->repos = $repos;
    }
}
