<?php

declare(strict_types=1);

namespace LaminasTest\Log\Formatter;

use ArrayIterator;
use DateTime;
use EmptyIterator;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Formatter\Simple;
use LaminasTest\Log\TestAsset\StringObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function fopen;
use function range;

class SimpleTest extends TestCase
{
    public function testConstructorThrowsOnBadFormatString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a string');
        new Simple(1);
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testConstructorWithOptions($dateTimeFormat): void
    {
        $options   = ['dateTimeFormat' => $dateTimeFormat, 'format' => '%timestamp%'];
        $formatter = new class ($options) extends Simple {
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
        $date   = new DateTime('2012-08-28T18:15:00Z');
        $fields = [
            'timestamp'    => $date,
            'message'      => 'foo',
            'priority'     => 42,
            'priorityName' => 'bar',
            'extra'        => [],
        ];

        $outputExpected = '2012-08-28T18:15:00+00:00 bar (42): foo';
        $formatter      = new Simple();

        $this->assertEquals($outputExpected, $formatter->format($fields));
    }

    public function testFormatAllTypes(): void
    {
        $date        = new DateTime('2012-08-28T18:15:00Z');
        $object      = new stdClass();
        $object->foo = 'bar';
        $fields      = [
            'timestamp'    => $date,
            'message'      => 'foo',
            'priority'     => 42,
            'priorityName' => 'bar',
            'extra'        => [
                'float'             => 0.2,
                'boolean'           => false,
                'array_empty'       => [],
                'array'             => range(0, 4),
                'traversable_empty' => new EmptyIterator(),
                'traversable'       => new ArrayIterator(['id', 42]),
                'null'              => null,
                'object_empty'      => new stdClass(),
                'object'            => $object,
                'string object'     => new StringObject(),
                'resource'          => fopen('php://stdout', 'w'),
            ],
        ];

        $outputExpected = '2012-08-28T18:15:00+00:00 bar (42): foo {'
            . '"float":0.2,'
            . '"boolean":false,'
            . '"array_empty":"[]",'
            . '"array":"[0,1,2,3,4]",'
            . '"traversable_empty":"[]",'
            . '"traversable":"[\"id\",42]",'
            . '"null":null,'
            . '"object_empty":"object(stdClass) {}",'
            . '"object":"object(stdClass) {\"foo\":\"bar\"}",'
            . '"string object":"Hello World",'
            . '"resource":"resource(stream)"}';
        $formatter      = new Simple();

        $this->assertEquals($outputExpected, $formatter->format($fields));
    }

    public function testFormatExtraArrayKeyWithNonArrayValue(): void
    {
        $date   = new DateTime('2012-08-28T18:15:00Z');
        $fields = [
            'timestamp'    => $date,
            'message'      => 'foo',
            'priority'     => 42,
            'priorityName' => 'bar',
            'extra'        => '',
        ];

        $outputExpected = '2012-08-28T18:15:00+00:00 bar (42): foo';
        $formatter      = new Simple();

        $this->assertEquals($outputExpected, $formatter->format($fields));
    }

    public function testFormatExtraArrayKeyWithNullValue(): void
    {
        $date   = new DateTime('2012-08-28T18:15:00Z');
        $fields = [
            'timestamp'    => $date,
            'message'      => 'foo',
            'priority'     => 42,
            'priorityName' => 'bar',
            'extra'        => null,
        ];

        $outputExpected = '2012-08-28T18:15:00+00:00 bar (42): foo';
        $formatter      = new Simple();

        $this->assertEquals($outputExpected, $formatter->format($fields));
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testCustomDateTimeFormat($dateTimeFormat): void
    {
        $date      = new DateTime();
        $event     = ['timestamp' => $date];
        $formatter = new Simple('%timestamp%', $dateTimeFormat);

        $this->assertEquals($date->format($dateTimeFormat), $formatter->format($event));
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat): void
    {
        $date      = new DateTime();
        $event     = ['timestamp' => $date];
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
        $message   = 'custom message';
        $exception = new RuntimeException($message);
        $event     = [
            'timestamp'    => new DateTime(),
            'message'      => 'Application error',
            'priority'     => 2,
            'priorityName' => 'CRIT',
            'extra'        => [$exception],
        ];

        $formatter = new Simple();
        $output    = $formatter->format($event);

        $this->assertStringContainsString($message, $output);
    }

    public function testAllowsSpecifyingFormatAsConstructorArgument(): void
    {
        $format    = '[%timestamp%] %message%';
        $formatter = new Simple($format);
        $this->assertEquals($format, $formatter->format([]));
    }
}
