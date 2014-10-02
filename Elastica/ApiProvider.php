<?php

namespace Rs\IssuesBundle\Elastica;

use FOS\ElasticaBundle\Provider\ProviderInterface;
use Rs\IssuesBundle\Storage\Storage;
use Rs\IssuesBundle\Synchronizer\Synchronizer;

/**
 * ApiProvider
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class ApiProvider implements ProviderInterface
{
    /**
     * @var Synchronizer[]
     */
    private $synchronizers;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param Synchronizer $synchronizer
     */
    public function addSynchronizer(Synchronizer $synchronizer)
    {
        $this->synchronizers[] = $synchronizer;
    }

    /**
     * @inheritdoc
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        foreach ($this->synchronizers as $synchronizer) {
            $synchronizer->synchronize(function ($message) use ($loggerClosure) {
                $message = preg_replace('/"(.*)"/', '<info>$1</info>', $message);
                $message = preg_replace('/\!(.*)\!/', '<error>$1</error>', $message);

                $loggerClosure($message);
            });
        }
    }
}
