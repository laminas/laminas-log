<?php

namespace Laminas\Log;

/**
 * Logger aware interface
 */
interface LoggerAwareInterface
{
    /**
     * Set logger instance
     *
     * @param LoggerInterface
     * @return void
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Get logger instance. Currently commented out as this would possibly break
     * existing implementations.
     *
     * @return null|LoggerInterface
     */
    // public function getLogger();
}
