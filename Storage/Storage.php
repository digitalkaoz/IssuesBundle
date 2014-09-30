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
     * removes old issues and projects
     */
    public function cleanup();

    /**
     * @param Project $project
     */
    public function saveProject(Project $project);

    /**
     * @param Issue   $issue
     * @param Project $project
     */
    public function saveIssue(Issue $issue, Project $project);

    /**
     * @return Project[]
     */
    public function getProjects();

    /**
     * @param string $projectId
     * @return Issue[]
     */
    public function getIssues($projectId);
}
