<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log\Writer;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage Writer
 */
class Mock extends AbstractWriter
{
    /**
     * array of log events
     *
     * @var array
     */
    public $events = array();

    /**
     * shutdown called?
     *
     * @var boolean
     */
    public $shutdown = false;

    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    public function doWrite(array $event)
    {
        $this->events[] = $event;
    }

    /**
     * Record shutdown
     *
     * @return void
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }
}
