<?php

declare(strict_types=1);

namespace LaminasTest\Log\Filter;

use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Filter\Priority;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Log
 */
class PriorityTest extends TestCase
{
    public function testComparisonDefaultsToLessThanOrEqual(): void
    {
        // accept at or below priority 2
        $filter = new Priority(2);

        $this->assertTrue($filter->filter(['priority' => 2]));
        $this->assertTrue($filter->filter(['priority' => 1]));
        $this->assertFalse($filter->filter(['priority' => 3]));
    }

    public function testComparisonOperatorCanBeChanged(): void
    {
        // accept above priority 2
        $filter = new Priority(2, '>');

        $this->assertTrue($filter->filter(['priority' => 3]));
        $this->assertFalse($filter->filter(['priority' => 2]));
        $this->assertFalse($filter->filter(['priority' => 1]));
    }

    public function testConstructorThrowsOnInvalidPriority(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a number');
        new Priority('foo');
    }

    public function testComparisonStringSupport(): void
    {
        // accept at or below priority '2'
        $filter = new Priority('2');

        $this->assertTrue($filter->filter(['priority' => 2]));
        $this->assertTrue($filter->filter(['priority' => 1]));
        $this->assertFalse($filter->filter(['priority' => 3]));
    }
}
