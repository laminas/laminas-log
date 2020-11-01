<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use ArrayObject;
use DateTime;
use Laminas\Log\Filter\Timestamp as TimestampFilter;
use PHPUnit\Framework\TestCase;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 *
 * @covers \Laminas\Log\Filter\Timestamp
 */
class TimestampTest extends TestCase
{
    /**
     * @dataProvider dateTimeDataProvider
     */
    public function testComparisonWhenValueIsSuppliedAsDateTimeObject(
        $timestamp,
        $dateTimeValue,
        $operator,
        $expectation
    ) {
        $filter = new TimestampFilter($dateTimeValue, null, $operator);

        $result = $filter->filter(['timestamp' => $timestamp]);

        if ($expectation === true) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @dataProvider datePartDataProvider
     */
    public function testComparisonWhenValueIsSuppliedAsDatePartValue(
        $timestamp,
        $datePartVal,
        $datePartChar,
        $operator,
        $expectation
    ) {
        $filter = new TimestampFilter($datePartVal, $datePartChar, $operator);

        $result = $filter->filter(['timestamp' => $timestamp]);

        if ($expectation === true) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function testConstructorThrowsOnInvalidValue(): void
    {
        $this->expectException(\Laminas\Log\Exception\InvalidArgumentException::class);
        new TimestampFilter('foo');
    }

    public function testConstructorThrowsWhenDateFormatCharIsMissing(): void
    {
        $this->expectException(\Laminas\Log\Exception\InvalidArgumentException::class);
        new TimestampFilter(3);
    }

    public function testConstructorThrowsOnUnsupportedComparisonOperator(): void
    {
        $this->expectException(\Laminas\Log\Exception\InvalidArgumentException::class);
        new TimestampFilter(10, 'H', 'foobar');
    }

    /**
     * @dataProvider filterCreationDataProvider
     */
    public function testFilterCreatedFromArray($config): void
    {
        $filter = new class($config) extends TimestampFilter {
            public function getDateFormatChar(): ?string
            {
                return $this->dateFormatChar;
            }

            public function getOperator(): string
            {
                return $this->operator;
            }

            public function getValue()
            {
                return $this->value;
            }
        };

        $this->assertSame($config['value'], $filter->getValue());
        $this->assertSame($config['dateFormatChar'], $filter->getDateFormatChar());
        $this->assertSame($config['operator'], $filter->getOperator());
    }

    public function filterCreationDataProvider(): array
    {
        return [
            'array'       => [['value' => 10, 'dateFormatChar' => 'm', 'operator' => '==',]],
            'traversable' => [new ArrayObject(['value' => 10, 'dateFormatChar' => 'm', 'operator' => '==',])],
        ];
    }

    /**
     * @param array $message
     *
     * @dataProvider ignoredMessages
     */
    public function testIgnoresMessagesWithoutTimestamp(array $message): void
    {
        $filter = new TimestampFilter(new DateTime('-10 years'));

        $this->assertFalse($filter->filter($message));
    }

    public function dateTimeDataProvider()
    {
        $march2 = new DateTime('2014-03-02');
        $march3 = new DateTime('2014-03-03');

        return [
            [new DateTime('2014-03-03'), new DateTime('2014-03-03'), '>=', true],
            [new DateTime('2014-10-10'), new DateTime('2014-03-03'),'>=', true],
            [new DateTime('2014-03-03'), new DateTime('2014-10-10'), 'gt', false],
            [new DateTime('2013-03-03'), new DateTime('2014-03-03'), 'ge', false],
            [new DateTime('2014-03-03'), new DateTime('2014-03-03'), '==', true],
            [new DateTime('2014-02-02'), new DateTime('2014-03-03'), '<', true],
            [new DateTime('2014-03-03'), new DateTime('2014-03-03'), 'lt', false],
            [$march3->getTimestamp(), new DateTime('2014-03-03'), 'lt', false],
            [$march2->getTimestamp(), new DateTime('2014-03-03'), 'lt', true],
            [(string) $march3->getTimestamp(), new DateTime('2014-03-03'), 'lt', false],
            [(string) $march2->getTimestamp(), new DateTime('2014-03-03'), 'lt', true],
        ];
    }

    public function datePartDataProvider()
    {
        return [
            [new DateTime('2014-03-03 10:15:00'), 10, 'H', '==', true],
            [new DateTime('2013-03-03 22:00:00'), 10, 'H', '=', false],
            [new DateTime('2014-03-04 10:15:00'), 3, 'd', 'gt', true],
            [new DateTime('2014-03-04 10:15:00'), 10, 'd', '<', true],
            [new DateTime('2014-03-03 10:15:00'), 1, 'm', 'eq', false],
            [new DateTime('2014-03-03 10:15:00'), 2, 'm', 'ge', true],
            [new DateTime('2014-03-03 10:15:00'), 20, 'H', '!=', true],
        ];
    }

    public function ignoredMessages()
    {
        return [
            [[]],
            [['hello world']],
            [['timestamp' => null]],
        ];
    }
}
