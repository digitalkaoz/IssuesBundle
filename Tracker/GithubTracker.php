<?php

namespace Rs\IssuesBundle\Tracker;
use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Rs\Issues\BadgeFactory;


/**
 * GithubTracker
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class GithubTracker extends \Rs\Issues\Github\GithubTracker
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param string       $token
     * @param Client       $client
     * @param BadgeFactory $badgeFactory
     */
    public function __construct($token = null, Client $client = null, BadgeFactory $badgeFactory = null)
    {
        $this->client = $client ?: new Client(new CachedHttpClient());

        if ($token) {
            $this->client->authenticate($token, null, Client::AUTH_HTTP_PASSWORD);
        }

        parent::__construct($token, $client, $badgeFactory);
    }

    public function getClient()
    {
        return $this->client;
    }
} 