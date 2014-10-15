<?php

namespace Rs\IssuesBundle\Storage;

use Rs\Issues\Issue;
use Rs\Issues\Project;

/**
 * Storage
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
interface Storage
{
    /**
     * remove old issues and projects
     */
    public function cleanup();

    /**
     * save a Project and all its Issues
     *
     * @param Project $project
     */
    public function saveProject(Project $project);

    /**
     * get all imported Projects
     *
     * @return Project[]
     */
    public function getProjects();

    /**
     * get all Issues for the provided Project-Id
     *
     * @param  string  $projectId
     * @return Issue[]
     */
    public function getIssues($projectId);
}
