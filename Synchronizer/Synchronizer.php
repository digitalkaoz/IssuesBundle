<?php

namespace Rs\IssuesBundle\Synchronizer;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
interface Synchronizer
{
    /**
     * synchronizes all Projects and Issues from the Tracker into the Storage
     *
     * @param \Closure $cb
     */
    public function synchronize($cb = null);

    /**
     * set the repositories to synchronize
     *
     * @param array $repos
     */
    public function setRepos(array $repos);
}
