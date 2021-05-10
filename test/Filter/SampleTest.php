<?php

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Sample;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function testConstructorThrowsOnInvalidSampleRate(): void
    {
        $this->expectException('Laminas\Log\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('must be numeric');
        new Sample('bar');
    }

    public function testSampleLimit0(): void
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

    public function testSampleLimit1(): void
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
