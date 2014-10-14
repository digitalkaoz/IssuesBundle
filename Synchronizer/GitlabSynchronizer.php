<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Project;
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

            $fetchedRepos = $tracker->findProjects($repository);

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
