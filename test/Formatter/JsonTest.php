<?php

declare(strict_types=1);

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\Json;
use PHPUnit\Framework\TestCase;

use function json_decode;

class JsonTest extends TestCase
{
    public function testDefaultFormat(): void
    {
        $date = new DateTime();
        $f    = new Json();
        $line = $f->format(['timestamp' => $date, 'message' => 'foo', 'priority' => 42]);
        $json = json_decode($line);

        $this->assertEquals($date->format('c'), $json->timestamp);
        $this->assertEquals('foo', $json->message);
        $this->assertEquals((string) 42, $json->priority);
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
    public function testSetDateTimeFormat($dateTimeFormat): void
    {
        $date = new DateTime();
        $f    = new Json();
        $f->setDateTimeFormat($dateTimeFormat);

        $line = $f->format(['timestamp' => $date]);
        $json = json_decode($line);

        $this->assertEquals($date->format($dateTimeFormat), $json->timestamp);
    }
}
