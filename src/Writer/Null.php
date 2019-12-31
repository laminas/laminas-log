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
class Null extends AbstractWriter
{
    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {}
}
