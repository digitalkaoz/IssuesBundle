<?php

namespace Rs\IssuesBundle\Synchronizer;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
interface Synchronizer
{
    /**
     * @param \Closure $cb
     */
    public function synchronize($cb = null);
}
