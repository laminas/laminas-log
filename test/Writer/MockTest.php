<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\Mock as MockWriter;
use PHPUnit\Framework\TestCase;

class MockTest extends TestCase
{
    public function testWrite()
    {
        $writer = new MockWriter();
        $this->assertSame([], $writer->events);

        $fields = ['foo' => 'bar'];
        $writer->write($fields);
        $this->assertSame([$fields], $writer->events);
    }
}
