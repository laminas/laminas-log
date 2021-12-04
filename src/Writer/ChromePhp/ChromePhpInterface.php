<?php

declare(strict_types=1);

namespace Laminas\Log\Writer\ChromePhp;

interface ChromePhpInterface
{
    /**
     * Log an error message
     *
     * @param string $line
     */
    public function error($line);

    /**
     * Log a warning
     *
     * @param string $line
     */
    public function warn($line);

    /**
     * Log informational message
     *
     * @param string $line
     */
    public function info($line);

    /**
     * Log a trace
     *
     * @param string $line
     */
    public function trace($line);

    /**
     * Log a message
     *
     * @param string $line
     */
    public function log($line);
}
