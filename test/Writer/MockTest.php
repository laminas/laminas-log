<?php

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\Mock as MockWriter;
use PHPUnit\Framework\TestCase;

class MockTest extends TestCase
{
    public function testWrite(): void
    {
        $writer = new MockWriter();
        $this->assertSame([], $writer->events);

        $fields = ['foo' => 'bar'];
        $writer->write($fields);
        $this->assertSame([$fields], $writer->events);
    }
}
