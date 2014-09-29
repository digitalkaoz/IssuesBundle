<?php

namespace Rs\IssuesBundle\Storage;

use Elastica\Document;
use Elastica\Query\MatchAll;
use Elastica\Type;
use Rs\Issues\Issue;
use Rs\Issues\Project;

/**
 * ElasticsearchStorage
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class ElasticsearchStorage implements Storage
{
    /**
     * @var Type
     */
    private $projectType;
    /**
     * @var Type
     */
    private $issueType;

    /**
     * @param Type $projectType
     * @param Type $issueType
     */
    public function __construct(Type $projectType, Type $issueType)
    {
        $this->projectType = $projectType;
        $this->issueType = $issueType;
    }

    /**
     * @inheritdoc
     */
    public function saveProject(Project $project)
    {
        $projectData = array(
            'name'        => $project->getName(),
            'url'         => $project->getUrl(),
            'description' => $project->getDescription(),
            'type'        => $project->getType(),
            'badges'      => $project->getBadges()
        );

        $doc = new Document(spl_object_hash($project), $projectData, 'project', 'projects');
        $this->projectType->addDocument($doc);

        return $doc;
    }

    /**
     * @inheritdoc
     */
    public function saveIssue(Issue $issue, Project $project)
    {
        $issueData = array(
            'title'         => $issue->getTitle(),
            'text'          => $issue->getText(),
            'state'         => $issue->getState(),
            'created_at'    => $issue->getCreatedAt()->getTimestamp(),
            'closed_at'     => $issue->getClosedAt() ? $issue->getClosedAt()->getTimestamp() : null,
            'updated_at'    => $issue->getUpdatedAt() ? $issue->getUpdatedAt()->getTimestamp() : null,
            'url'           => $issue->getUrl(),
            'comment_count' => $issue->getCommentCount(),
            'owner'         => $issue->getOwner(),
            'owner_url'     => $issue->getOwnerUrl(),
            'assignee'      => $issue->getAssignee(),
            'assignee_url'  => $issue->getAssigneeUrl(),
            'type'          => $issue->getType(),
            'number'        => $issue->getNumber(),
        );

        $doc = new Document(spl_object_hash($issue), $issueData, 'issue', 'projects');
        $doc->setParent(spl_object_hash($project));
        $this->issueType->addDocument($doc);

        return $doc;
    }

    /**
     * removes old issues and projects
     */
    public function cleanup()
    {
        $this->projectType->deleteByQuery(new MatchAll());
        $this->issueType->deleteByQuery(new MatchAll());
    }
}
