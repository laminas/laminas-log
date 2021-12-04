<?php

declare(strict_types=1);

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\Db as DbFormatter;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function testDefaultDateTimeFormat(): void
    {
        $formatter = new DbFormatter();
        $this->assertEquals(DbFormatter::DEFAULT_DATETIME_FORMAT, $formatter->getDateTimeFormat());
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat): void
    {
        $formatter = new DbFormatter();
        $formatter->setDateTimeFormat($dateTimeFormat);

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
    }

    /**
     * @return array
     */
    public function provideDateTimeFormats()
    {
        return [
            ['r'],
            ['U'],
            [DateTime::RSS],
        ];
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testAllowsSpecifyingDateTimeFormatAsConstructorArgument($dateTimeFormat): void
    {
        $formatter = new DbFormatter($dateTimeFormat);

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
    }

    public function testFormatDateTimeInEvent(): void
    {
        $datetime  = new DateTime();
        $event     = ['timestamp' => $datetime];
        $formatter = new DbFormatter();

        $format = DbFormatter::DEFAULT_DATETIME_FORMAT;
        $this->assertContains($datetime->format($format), $formatter->format($event));
    }
}
