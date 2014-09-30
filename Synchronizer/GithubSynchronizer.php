<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Tracker;
use Rs\IssuesBundle\Storage\Storage;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class GithubSynchronizer implements Synchronizer
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

    /**
     * @inheritdoc
     */
    public function synchronize($cb = null)
    {
        foreach ($this->repos as $repo) {
            if (1 !== preg_match('/^[a-zA-Z0-9\.-]+\/[a-zA-Z0-9\.-]+/', $repo)) {
                $this->expand($repo, $cb);
            } else {
                $this->fetch($repo, $cb);
            }
        }
    }

    /**
     * @param string $repo
     * @param \Closure $cb
     */
    private function fetch($repo, $cb =null)
    {
        try {
            $project = $this->tracker->getProject($repo);

            $this->storage->saveProject($project);

            foreach ($project->getIssues() as $issue) {
                $this->storage->saveIssue($issue, $project);
            }

            if (is_callable($cb)) {
                $cb(sprintf('synchronized "%s"', $repo));
            }
        } catch (\Exception $e) {
            if (is_callable($cb)) {
                $cb(sprintf('failed !%s! with %s', $repo, $e->getMessage()));
            }
        }
    }

    private function expand($repo, $cb = null)
    {
        preg_match('/([a-zA-Z0-9\.-]+)\/.+/', $repo, $matches);

        $repos = $this->tracker->getClient()->api('user')->repositories($matches[1]);

        if (1 === preg_match('/([a-zA-Z0-9\.-])\/\*/', $repo, $matches)) {
            // org/*
            foreach ($repos as $repo) {
                $this->fetch($repo['full_name'], $cb);
            }
        } else {
            // org/[Foo|Bar]+
            // org/(!...)
            foreach ($repos as $key => $expandedRepo) {
                if (preg_match('/'.str_replace('/', '\/', $repo).'/', $expandedRepo['full_name'])) {
                    $this->fetch($expandedRepo['full_name'], $cb);
                }
            }
        }
    }
}
