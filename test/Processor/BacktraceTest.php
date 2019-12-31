<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Processor;

use Laminas\Log\Processor\Backtrace;

/**
 * @group      Laminas_Log
 */
class BacktraceTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $processor = new Backtrace();

        $event = array(
                'timestamp'    => '',
                'priority'     => 1,
                'priorityName' => 'ALERT',
                'message'      => 'foo',
                'extra'        => array()
        );

        $event = $processor->process($event);

        $this->assertArrayHasKey('file', $event['extra']);
        $this->assertArrayHasKey('line', $event['extra']);
        $this->assertArrayHasKey('class', $event['extra']);
        $this->assertArrayHasKey('function', $event['extra']);
    }
}
