<?php

namespace Rs\IssuesBundle\Elastica;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * Callback
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class Callback extends \FOS\ElasticaBundle\Serializer\Callback
{
    public function serialize($object)
    {
        $context = $this->serializer instanceof SerializerInterface ? SerializationContext::create()->enableMaxDepthChecks()->setSerializeNull(true) : array();

        if ($this->groups) {
            $context->setGroups($this->groups);
        }

        if ($this->version) {
            $context->setVersion($this->version);
        }

        return $this->serializer->serialize($object, 'json', $context);
    }

}
