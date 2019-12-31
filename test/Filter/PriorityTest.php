<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Priority;

/**
 * @group      Laminas_Log
 */
class PriorityTest extends \PHPUnit_Framework_TestCase
{
    public function testComparisonDefaultsToLessThanOrEqual()
    {
        // accept at or below priority 2
        $filter = new Priority(2);

        $this->assertTrue($filter->filter(['priority' => 2]));
        $this->assertTrue($filter->filter(['priority' => 1]));
        $this->assertFalse($filter->filter(['priority' => 3]));
    }

    public function testComparisonOperatorCanBeChanged()
    {
        // accept above priority 2
        $filter = new Priority(2, '>');

        $this->assertTrue($filter->filter(['priority' => 3]));
        $this->assertFalse($filter->filter(['priority' => 2]));
        $this->assertFalse($filter->filter(['priority' => 1]));
    }

    public function testConstructorThrowsOnInvalidPriority()
    {
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'must be a number');
        new Priority('foo');
    }

    public function testComparisonStringSupport()
    {
        // accept at or below priority '2'
        $filter = new Priority('2');

        $this->assertTrue($filter->filter(['priority' => 2]));
        $this->assertTrue($filter->filter(['priority' => 1]));
        $this->assertFalse($filter->filter(['priority' => 3]));
    }
}
