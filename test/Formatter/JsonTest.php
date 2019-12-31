<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testDefaultFormat()
    {
        $date = new DateTime();
        $f = new Json();
        $line = $f->format(['timestamp' => $date, 'message' => 'foo', 'priority' => 42]);
        $json = json_decode($line);

        $this->assertEquals($date->format('c'), $json->timestamp);
        $this->assertEquals('foo', $json->message);
        $this->assertEquals((string)42, $json->priority);
    }

    public function provideDateTimeFormats()
    {
        return [
            ['r'],
            ['U'],
        ];
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat)
    {
        $date = new DateTime();
        $f = new Json();
        $f->setDateTimeFormat($dateTimeFormat);

        $line = $f->format(['timestamp' => $date]);
        $json = json_decode($line);

        $this->assertEquals($date->format($dateTimeFormat), $json->timestamp);
    }
}
