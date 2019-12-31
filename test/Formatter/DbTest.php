<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\Db as DbFormatter;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class DbTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultDateTimeFormat()
    {
        $formatter = new DbFormatter();
        $this->assertEquals(DbFormatter::DEFAULT_DATETIME_FORMAT, $formatter->getDateTimeFormat());
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat)
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
        return array(
            array('r'),
            array('U'),
            array(DateTime::RSS),
        );
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testAllowsSpecifyingDateTimeFormatAsConstructorArgument($dateTimeFormat)
    {
        $formatter = new DbFormatter($dateTimeFormat);

        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
    }

    public function testFormatDateTimeInEvent()
    {
        $datetime = new DateTime();
        $event = array('timestamp' => $datetime);
        $formatter = new DbFormatter();

        $format = DbFormatter::DEFAULT_DATETIME_FORMAT;
        $this->assertContains($datetime->format($format), $formatter->format($event));
    }
}
