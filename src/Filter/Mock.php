<?php

declare(strict_types=1);

namespace Laminas\Log\Filter;

class Mock implements FilterInterface
{
    /**
     * array of log events
     *
     * @var array
     */
    public $events = [];

    /**
     * Returns TRUE to accept the message
     *
     * @param array $event event data
     * @return bool
     */
    public function filter(array $event)
    {
        $this->events[] = $event;
        return true;
    }
}
