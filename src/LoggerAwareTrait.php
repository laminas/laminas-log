<?php

declare(strict_types=1);

namespace Laminas\Log;

trait LoggerAwareTrait
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * Set logger object
     *
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get logger object
     *
     * @return null|LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
