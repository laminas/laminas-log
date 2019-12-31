<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Processor;

use Laminas\Log\Processor\Backtrace;
use PHPUnit\Framework\TestCase;

class BacktraceTest extends TestCase
{
    private $processor;

    protected function setUp()
    {
        $this->processor = new Backtrace();
    }

    public function testProcess()
    {
        $event = [
                'timestamp'    => '',
                'priority'     => 1,
                'priorityName' => 'ALERT',
                'message'      => 'foo',
                'extra'        => []
        ];

        $event = $this->processor->process($event);

        $this->assertArrayHasKey('file', $event['extra']);
        $this->assertArrayHasKey('line', $event['extra']);
        $this->assertArrayHasKey('class', $event['extra']);
        $this->assertArrayHasKey('function', $event['extra']);
    }

    public function testConstructorAcceptsOptionalIgnoredNamespaces()
    {
        $this->assertSame(['Laminas\\Log'], $this->processor->getIgnoredNamespaces());

        $processor = new Backtrace(['ignoredNamespaces' => ['Foo\\Bar']]);
        $this->assertSame(['Laminas\\Log', 'Foo\\Bar'], $processor->getIgnoredNamespaces());
    }
}
