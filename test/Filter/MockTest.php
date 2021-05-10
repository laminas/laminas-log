<?php

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Mock as MockFilter;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Log
 */
class MockTest extends TestCase
{
    public function testWrite(): void
    {
        $filter = new MockFilter();
        $this->assertSame([], $filter->events);

        $fields = ['foo' => 'bar'];
        $this->assertTrue($filter->filter($fields));
        $this->assertSame([$fields], $filter->events);
    }
}
