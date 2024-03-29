<?php

declare(strict_types=1);

namespace LaminasTest\Log\Formatter;

use ArrayIterator;
use DateTime;
use EmptyIterator;
use Laminas\Log\Formatter\Base as BaseFormatter;
use LaminasTest\Log\TestAsset\StringObject;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fopen;
use function range;

class BaseTest extends TestCase
{
    public function testDefaultDateTimeFormat(): void
    {
        $formatter = new BaseFormatter();
        $this->assertEquals(BaseFormatter::DEFAULT_DATETIME_FORMAT, $formatter->getDateTimeFormat());
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testAllowsSpecifyingDateTimeFormatAsConstructorArgument($dateTimeFormat): void
    {
        $formatter = new BaseFormatter($dateTimeFormat);

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
    public function testSetDateTimeFormat($dateTimeFormat): void
    {
        $formatter = new BaseFormatter();
        $formatter->setDateTimeFormat($dateTimeFormat);

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormatInConstructor($dateTimeFormat): void
    {
        $options   = ['dateTimeFormat' => $dateTimeFormat];
        $formatter = new BaseFormatter($options);

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
    }

    public function testFormatAllTypes(): void
    {
        $datetime    = new DateTime();
        $object      = new stdClass();
        $object->foo = 'bar';
        $formatter   = new BaseFormatter();

        $event          = [
            'timestamp' => $datetime,
            'priority'  => 1,
            'message'   => 'tottakai',
            'extra'     => [
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
        $outputExpected = [
            'timestamp' => $datetime->format($formatter->getDateTimeFormat()),
            'priority'  => 1,
            'message'   => 'tottakai',
            'extra'     => [
                'boolean'           => false,
                'float'             => 0.2,
                'array_empty'       => '[]',
                'array'             => '[0,1,2,3,4]',
                'traversable_empty' => '[]',
                'traversable'       => '["id",42]',
                'null'              => null,
                'object_empty'      => 'object(stdClass) {}',
                'object'            => 'object(stdClass) {"foo":"bar"}',
                'string object'     => 'Hello World',
                'resource'          => 'resource(stream)',
            ],
        ];

        $this->assertEquals($outputExpected, $formatter->format($event));
    }

    public function testFormatNoInfiniteLoopOnSelfReferencingArrayValues(): void
    {
        $datetime  = new DateTime();
        $formatter = new BaseFormatter();

        $selfRefArr               = [];
        $selfRefArr['selfRefArr'] = &$selfRefArr;

        $event = [
            'timestamp' => $datetime,
            'priority'  => 1,
            'message'   => 'tottakai',
            'extra'     => [
                'selfRefArr' => $selfRefArr,
            ],
        ];

        $outputExpected = [
            'timestamp' => $datetime->format($formatter->getDateTimeFormat()),
            'priority'  => 1,
            'message'   => 'tottakai',
            'extra'     => [
                'selfRefArr' => '',
            ],
        ];

        $this->assertEquals($outputExpected, $formatter->format($event));
    }

    public function testFormatExtraArrayKeyWithNonArrayValue(): void
    {
        $formatter = new BaseFormatter();

        $event          = [
            'message' => 'Hi',
            'extra'   => '',
        ];
        $outputExpected = [
            'message' => 'Hi',
            'extra'   => '',
        ];

        $this->assertEquals($outputExpected, $formatter->format($event));
    }
}
