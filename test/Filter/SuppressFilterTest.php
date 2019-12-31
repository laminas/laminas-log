<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\SuppressFilter;
use PHPUnit\Framework\TestCase;

class SuppressFilterTest extends TestCase
{
    protected function setUp()
    {
        $this->filter = new SuppressFilter();
    }

    public function testSuppressIsInitiallyOff()
    {
        $this->assertTrue($this->filter->filter([]));
    }

    public function testSuppressByConstructorBoolean()
    {
        $this->filter = new SuppressFilter(true);
        $this->assertFalse($this->filter->filter([]));
        $this->assertFalse($this->filter->filter([]));
    }

    public function testSuppressByConstructorArray()
    {
        $this->filter = new SuppressFilter(['suppress' => true]);
        $this->assertFalse($this->filter->filter([]));
        $this->assertFalse($this->filter->filter([]));
    }

    public function testConstructorThrowsOnInvalidSuppressValue()
    {
        $this->expectException('Laminas\Log\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Suppress must be a boolean');
        new SuppressFilter('foo');
    }

    public function testSuppressOn()
    {
        $this->filter->suppress(true);
        $this->assertFalse($this->filter->filter([]));
        $this->assertFalse($this->filter->filter([]));
    }

    public function testSuppressOff()
    {
        $this->filter->suppress(false);
        $this->assertTrue($this->filter->filter([]));
        $this->assertTrue($this->filter->filter([]));
    }

    public function testSuppressCanBeReset()
    {
        $this->filter->suppress(true);
        $this->assertFalse($this->filter->filter([]));
        $this->filter->suppress(false);
        $this->assertTrue($this->filter->filter([]));
        $this->filter->suppress(true);
        $this->assertFalse($this->filter->filter([]));
    }
}
