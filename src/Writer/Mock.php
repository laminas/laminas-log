<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

class Mock extends AbstractWriter
{
    /**
     * array of log events
     *
     * @var array
     */
    public $events = [];

    /**
     * shutdown called?
     *
     * @var bool
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
