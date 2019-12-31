<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Mock as MockFilter;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Log
 */
class MockTest extends TestCase
{
    public function testWrite()
    {
        $filter = new MockFilter();
        $this->assertSame([], $filter->events);

        $fields = ['foo' => 'bar'];
        $this->assertTrue($filter->filter($fields));
        $this->assertSame([$fields], $filter->events);
    }
}
