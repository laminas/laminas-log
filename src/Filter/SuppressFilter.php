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
 * @subpackage Filter
 */
class SuppressFilter implements FilterInterface
{
    /**
     * @var boolean
     */
    protected $accept = true;

    /**
     * This is a simple boolean filter.
     *
     * Call suppress(true) to suppress all log events.
     * Call suppress(false) to accept all log events.
     *
     * @param boolean $suppress Should all log events be suppressed?
     * @return void
     */
    public function suppress($suppress)
    {
        $this->accept = ! (bool) $suppress;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param array $event event data
     * @return boolean accepted?
     */
    public function filter(array $event)
    {
        return $this->accept;
    }
}
