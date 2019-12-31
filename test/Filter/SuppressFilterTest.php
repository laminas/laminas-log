<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\SuppressFilter;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class SuppressFilterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filter = new SuppressFilter();
    }

    public function testSuppressIsInitiallyOff()
    {
        $this->assertTrue($this->filter->filter(array()));
    }

    public function testSuppressOn()
    {
        $this->filter->suppress(true);
        $this->assertFalse($this->filter->filter(array()));
        $this->assertFalse($this->filter->filter(array()));
    }

    public function testSuppressOff()
    {
        $this->filter->suppress(false);
        $this->assertTrue($this->filter->filter(array()));
        $this->assertTrue($this->filter->filter(array()));
    }

    public function testSuppressCanBeReset()
    {
        $this->filter->suppress(true);
        $this->assertFalse($this->filter->filter(array()));
        $this->filter->suppress(false);
        $this->assertTrue($this->filter->filter(array()));
        $this->filter->suppress(true);
        $this->assertFalse($this->filter->filter(array()));
    }
}
