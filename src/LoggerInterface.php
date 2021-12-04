<?php

declare(strict_types=1);

namespace Laminas\Log;

use Traversable;

interface LoggerInterface
{
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function emerg($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function alert($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function crit($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function err($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function warn($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function notice($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function info($message, $extra = []);

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function debug($message, $extra = []);
}
