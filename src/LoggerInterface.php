<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log;

/**
 * @category   Laminas
 * @package    Laminas_Log
 */
interface LoggerInterface
{
    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function emerg($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function alert($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function crit($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function err($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function warn($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function notice($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function info($message, $extra = array());

    /**
     * @param string $message
     * @param array|\Traversable $extra
     * @return Loggabble
     */
    public function debug($message, $extra = array());
}
