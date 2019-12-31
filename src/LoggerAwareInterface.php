<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log;

use Laminas\Log\LoggerInterface;

/**
 * Logger aware interface
 *
 * @category   Laminas
 * @package    Laminas_Log
 */
interface LoggerAwareInterface
{
    public function setLogger(LoggerInterface $logger);
}
