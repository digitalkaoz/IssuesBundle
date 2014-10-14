<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Tracker;
use Rs\IssuesBundle\Storage\Storage;
use Rs\IssuesBundle\Tracker\TrackerFactory;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class JiraSynchronizer implements Synchronizer
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
     * @var TrackerFactory
     */
    private $trackerFactory;

    /**
     * @param Storage        $storage
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
            $this->fetch($repo, $cb);
        }
    }

    /**
     * @inheritdoc
     */
    public function setRepos(array $repos)
    {
        $this->repos = $repos;
    }

    /**
     * @param string   $repo
     * @param \Closure $cb
     */
    private function fetch($repo, $cb = null)
    {
        try {
            list($host, $repo, $username, $password) = explode(' ', $repo);
            $tracker = $this->trackerFactory->createJiraTracker($host, $username, $password);

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
}
