<?php

namespace Rs\IssuesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class IssuesController extends Controller
{
    public function projectsAction()
    {
        return new JsonResponse($this->get('rs_issues.storage')->getProjects());
    }

    public function issuesAction($project)
    {
        return new JsonResponse($this->get('rs_issues.storage')->getIssues($project));
    }
}
