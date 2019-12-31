<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\Simple;
use RuntimeException;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class SimpleTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorThrowsOnBadFormatString()
    {
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'must be a string');
        new Simple(1);
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testConstructorWithOptions($dateTimeFormat)
    {
        $options = array('dateTimeFormat' => $dateTimeFormat, 'format' => '%timestamp%');
        $formatter = new Simple($options);

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
        $this->assertAttributeEquals('%timestamp%', 'format', $formatter);
    }

    public function testDefaultFormat()
    {
        $date = new DateTime('2012-08-28T18:15:00Z');
        $fields = array(
            'timestamp'    => $date,
            'message'      => 'foo',
            'priority'     => 42,
            'priorityName' => 'bar',
            'extra'        => array()
        );

        $outputExpected = '2012-08-28T18:15:00+00:00 bar (42): foo';
        $formatter = new Simple();

        $this->assertEquals($outputExpected, $formatter->format($fields));
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testCustomDateTimeFormat($dateTimeFormat)
    {
        $date = new DateTime();
        $event = array('timestamp' => $date);
        $formatter = new Simple('%timestamp%', $dateTimeFormat);

        $this->assertEquals($date->format($dateTimeFormat), $formatter->format($event));
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat)
    {
        $date = new DateTime();
        $event = array('timestamp' => $date);
        $formatter = new Simple('%timestamp%');

        $this->assertSame($formatter, $formatter->setDateTimeFormat($dateTimeFormat));
        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
        $this->assertEquals($date->format($dateTimeFormat), $formatter->format($event));
    }

    public function provideDateTimeFormats()
    {
        return array(
            array('r'),
            array('U'),
        );
    }

    /**
     * @group Laminas-10427
     */
    public function testDefaultFormatShouldDisplayExtraInformations()
    {
        $message = 'custom message';
        $exception = new RuntimeException($message);
        $event = array(
            'timestamp'    => new DateTime(),
            'message'      => 'Application error',
            'priority'     => 2,
            'priorityName' => 'CRIT',
            'extra'        => array($exception),
        );

        $formatter = new Simple();
        $output = $formatter->format($event);

        $this->assertContains($message, $output);
    }

    public function testAllowsSpecifyingFormatAsConstructorArgument()
    {
        $format = '[%timestamp%] %message%';
        $formatter = new Simple($format);
        $this->assertEquals($format, $formatter->format(array()));
    }
}
