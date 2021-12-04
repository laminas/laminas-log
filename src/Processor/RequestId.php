<?php

declare(strict_types=1);

namespace Laminas\Log\Processor;

use function md5;

class RequestId implements ProcessorInterface
{
    /**
     * Request identifier
     *
     * @var string
     */
    protected $identifier;

    /**
     * Adds an identifier for the request to the log, unless one has already been set.
     *
     * This enables to filter the log for messages belonging to a specific request
     *
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        if (isset($event['extra']['requestId'])) {
            return $event;
        }

        if (! isset($event['extra'])) {
            $event['extra'] = [];
        }

        $event['extra']['requestId'] = $this->getIdentifier();
        return $event;
    }

    /**
     * Provide unique identifier for a request
     *
     * @return string
     */
    protected function getIdentifier()
    {
        if ($this->identifier) {
            return $this->identifier;
        }

        $identifier = (string) $_SERVER['REQUEST_TIME_FLOAT'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $identifier .= $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $identifier .= $_SERVER['REMOTE_ADDR'];
        }

        $this->identifier = md5($identifier);

        return $this->identifier;
    }
}
