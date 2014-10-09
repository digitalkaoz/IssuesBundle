<?php

namespace Rs\IssuesBundle\Tracker;
use Gitlab\Client;
use Rs\Issues\BadgeFactory;


/**
 * GitlabTracker
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class GitlabTracker extends \Rs\Issues\Gitlab\GitlabTracker
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $host
     * @param string $token
     * @param Client $client
     * @param BadgeFactory $badgeFactory
     */
    public function __construct($host, $token = null, Client $client = null, BadgeFactory $badgeFactory = null)
    {
        $this->client = $client ?: new Client($host);

        if ($token) {
            $this->client->authenticate($token, Client::AUTH_URL_TOKEN);
        }

        parent::__construct($host, $token, $client, $badgeFactory);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
} 