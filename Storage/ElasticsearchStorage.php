<?php

namespace Rs\IssuesBundle\Storage;

use Elastica\Document;
use Elastica\Query;
use Elastica\Filter;
use Elastica\Result;
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
        $this->projectType->deleteByQuery(new Query\MatchAll());
        $this->issueType->deleteByQuery(new Query\MatchAll());
    }

    /**
     * @return Result[]
     */
    public function getProjects()
    {
        $q = $this->createProjectQuery();
        $projectDocs = $this->projectType->search($q)->getResults();

        return $this->formatProjects($projectDocs);
    }

    /**
     * @param string $projectId
     * @return Result[]
     */
    public function getIssues($projectId)
    {
        $q = $this->createIssueQuery($projectId);
        $issuesDocs = $this->issueType->search($q)->getResults();

        return $this->formatIssues($issuesDocs);
    }

    /**
     * @return Query
     */
    private function createProjectQuery()
    {
        $tq = new Query\TopChildren(new Query\MatchAll(), 'issue');
        $tq->setParam('score', 'sum');

        //this is hacky, we dont need scoring, so we hack to put the issue count into the score
        $bq = new Query\Bool();
        $bq->addShould($tq);
        $bq->addShould(new Query\MatchAll());

        $q = new Query($bq);
        $q->setSize(99999);

        return $q;
    }

    /**
     * @param $projectId
     * @return Query
     */
    private function createIssueQuery($projectId)
    {
        $q = new Query();
        $q->setFilter(new Filter\HasParent(new Query\Term(array('_id' => $projectId)), 'project'));
        $q->setSize(99999);

        return $q;
    }

    /**
     * @param $projectDocs
     * @return array
     */
    private function formatProjects($projectDocs)
    {
        $projects = array_map(function (Result $result) {
            $data = $result->getData();
            $data['id'] = $result->getId();
            $data['issuesCount'] = floor($result->getScore());

            return $data;
        }, $projectDocs);

        $keys = array_column($projects, 'id');

        return array_combine($keys, $projects);
    }

    /**
     * @param $issuesDocs
     * @return array
     */
    private function formatIssues($issuesDocs)
    {
        return array_map(function (Result $result) {
            return $result->getData();
        }, $issuesDocs);
    }
}
