<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Processor;

use Laminas\Log\Processor\RequestId;

/**
 * @group      Laminas_Log
 */
class RequestIdTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $processor = new RequestId();

        $event = array(
            'timestamp'    => '',
            'priority'     => 1,
            'priorityName' => 'ALERT',
            'message'      => 'foo',
            'extra'        => array(),
        );

        $eventA = $processor->process($event);
        $this->assertArrayHasKey('requestId', $eventA['extra']);

        $eventB = $processor->process($event);
        $this->assertArrayHasKey('requestId', $eventB['extra']);

        $this->assertEquals($eventA['extra']['requestId'], $eventB['extra']['requestId']);
    }

    public function testProcessDoesNotOverwriteExistingRequestId()
    {
        $processor = new RequestId();

        $requestId = 'bar';

        $event = array(
            'timestamp'    => '',
            'priority'     => 1,
            'priorityName' => 'ALERT',
            'message'      => 'foo',
            'extra'        => array(
                'requestId' => $requestId,
            ),
        );

        $processedEvent = $processor->process($event);

        $this->assertArrayHasKey('requestId', $processedEvent['extra']);
        $this->assertSame($requestId, $processedEvent['extra']['requestId']);
    }
}
