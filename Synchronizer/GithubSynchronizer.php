<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Github\GithubTracker;
use Rs\Issues\Project;
use Rs\IssuesBundle\Storage\Storage;
use Rs\IssuesBundle\Tracker\TrackerFactory;

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
     * @var Storage
     */
    private $storage;
    /**
     * @var GithubTracker
     */
    private $tracker;
    /**
     * @var null
     */
    private $token;

    /**
     * @param Storage $storage
     * @param TrackerFactory $trackerFactory
     * @param string $token
     */
    public function __construct(Storage $storage, TrackerFactory $trackerFactory, $token = null)
    {
        $this->storage = $storage;
        $this->token = $token;
        $this->tracker = $trackerFactory->createGithubTracker($token);
    }

    /**
     * @inheritdoc
     */
    public function synchronize($cb = null)
    {
        foreach ($this->repos as $repo) {
            $fetchedRepos = $this->tracker->findProjects($repo);

            foreach ($fetchedRepos as $project) {
                $this->store($project, $cb);
            }
        }
    }

    /**
     * @param Project  $project
     * @param \Closure $cb
     */
    private function store(Project $project, $cb =null)
    {
        try {
            $this->storage->saveProject($project);

            if (is_callable($cb)) {
                $cb(sprintf('synchronized "%s"', $project->getName()));
            }
        } catch (\Exception $e) {
            if (is_callable($cb)) {
                $cb(sprintf('failed !%s! with %s', $project->getName(), $e->getMessage()));
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
