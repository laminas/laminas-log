<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log\Filter;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage Writer
 */
class Mock implements FilterInterface
{
    /**
     * array of log events
     *
     * @var array
     */
    public $events = array();

    /**
     * Returns TRUE to accept the message
     *
     * @param array $event event data
     * @return boolean
     */
    public function filter(array $event)
    {
        $this->events[] = $event;
        return true;
    }
}
