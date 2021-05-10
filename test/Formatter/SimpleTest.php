<?php

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\Simple;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SimpleTest extends TestCase
{
    public function testConstructorThrowsOnBadFormatString(): void
    {
        $this->expectException('Laminas\Log\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('must be a string');
        new Simple(1);
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testConstructorWithOptions($dateTimeFormat): void
    {
        $options = ['dateTimeFormat' => $dateTimeFormat, 'format' => '%timestamp%'];
        $formatter = new class($options) extends Simple {
            public function getFormat(): string
            {
                return $this->format;
            }
        };

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
        $this->assertSame('%timestamp%', $formatter->getFormat());
    }

    public function testDefaultFormat(): void
    {
        $date = new DateTime('2012-08-28T18:15:00Z');
        $fields = [
            'timestamp'    => $date,
            'message'      => 'foo',
            'priority'     => 42,
            'priorityName' => 'bar',
            'extra'        => []
        ];

        $outputExpected = '2012-08-28T18:15:00+00:00 bar (42): foo';
        $formatter = new Simple();

        $this->assertEquals($outputExpected, $formatter->format($fields));
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testCustomDateTimeFormat($dateTimeFormat): void
    {
        $date = new DateTime();
        $event = ['timestamp' => $date];
        $formatter = new Simple('%timestamp%', $dateTimeFormat);

        $this->assertEquals($date->format($dateTimeFormat), $formatter->format($event));
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat): void
    {
        $date = new DateTime();
        $event = ['timestamp' => $date];
        $formatter = new Simple('%timestamp%');

        $this->assertSame($formatter, $formatter->setDateTimeFormat($dateTimeFormat));
        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
        $this->assertEquals($date->format($dateTimeFormat), $formatter->format($event));
    }

    public function provideDateTimeFormats()
    {
        return [
            ['r'],
            ['U'],
        ];
    }

    /**
     * @group Laminas-10427
     */
    public function testDefaultFormatShouldDisplayExtraInformations(): void
    {
        $message = 'custom message';
        $exception = new RuntimeException($message);
        $event = [
            'timestamp'    => new DateTime(),
            'message'      => 'Application error',
            'priority'     => 2,
            'priorityName' => 'CRIT',
            'extra'        => [$exception],
        ];

        $formatter = new Simple();
        $output = $formatter->format($event);

        $this->assertStringContainsString($message, $output);
    }

    public function testAllowsSpecifyingFormatAsConstructorArgument(): void
    {
        $format = '[%timestamp%] %message%';
        $formatter = new Simple($format);
        $this->assertEquals($format, $formatter->format([]));
    }
}
