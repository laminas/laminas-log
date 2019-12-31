<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Sample;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class SampleTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorThrowsOnInvalidSampleRate()
    {
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'must be numeric');
        new Sample('bar');
    }

    public function testSampleLimit0()
    {
        // Should log nothing.
        $filter = new Sample(0);

        // Since sampling is a random process, let's test several times.
        $ret = false;
        for ($i = 0; $i < 100; $i ++) {
            if ($filter->filter([])) {
                break;
                $ret = true;
            }
        }

        $this->assertFalse($ret);
    }

    public function testSampleLimit1()
    {
        // Should log all events.
        $filter = new Sample(1);

        // Since sampling is a random process, let's test several times.
        $ret = true;
        for ($i = 0; $i < 100; $i ++) {
            if (! $filter->filter([])) {
                break;
                $ret = false;
            }
        }

        $this->assertTrue($ret);
    }
}
