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
        $response = $this->projectType->addObject($project);

        foreach ($project->getIssues() as $issue) {
            $doc = new Document();
            $doc->setParent($response->getData()['_id']);
            $this->issueType->addObject($issue, $doc);
        }
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
     * @return array
     */
    public function getProjects()
    {
        $q = $this->createProjectQuery();
        $projectDocs = $this->projectType->search($q)->getResults();

        return $this->formatProjects($projectDocs);
    }

    /**
     * @param  string   $projectId
     * @return array
     */
    public function getIssues($projectId)
    {
        $q = $this->createIssueQuery($projectId);
        $issuesDocs = $this->issueType->search($q)->getResults();

        return array_map(function (Result $result) {
            return $result->getData();
        }, $issuesDocs);
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
        $q->setPostFilter(new Filter\HasParent(new Query\Term(array('_id' => $projectId)), 'project'));
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
}
