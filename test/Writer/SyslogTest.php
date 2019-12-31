<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Syslog as SyslogWriter;
use LaminasTest\Log\TestAsset\CustomSyslogWriter;

/**
 * @group      Laminas_Log
 */
class SyslogTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $fields = array(
            'message' => 'foo',
            'priority' => LOG_NOTICE
        );
        $writer = new SyslogWriter();
        $writer->write($fields);
    }

    /**
     * @group Laminas-7603
     */
    public function testThrowExceptionValueNotPresentInFacilities()
    {
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'Invalid log facility provided');
        $writer = new SyslogWriter();
        $writer->setFacility(LOG_USER * 1000);
    }

    /**
     * @group Laminas-7603
     */
    public function testThrowExceptionIfFacilityInvalidInWindows()
    {
        if ('WIN' != strtoupper(substr(PHP_OS, 0, 3))) {
            $this->markTestSkipped('Run only in windows');
        }
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'Only LOG_USER is a valid');
        $writer = new SyslogWriter();
        $writer->setFacility(LOG_AUTH);
    }

    /**
     * @group Laminas-8953
     */
    public function testFluentInterface()
    {
        $writer   = new SyslogWriter();
        $instance = $writer->setFacility(LOG_USER)
                           ->setApplicationName('my_app');

        $this->assertTrue($instance instanceof SyslogWriter);
    }

    /**
     * @group Laminas-10769
     */
    public function testPastFacilityViaConstructor()
    {
        $writer = new CustomSyslogWriter(array('facility' => LOG_USER));
        $this->assertEquals(LOG_USER, $writer->getFacility());
    }

    /**
     * @group Laminas-8382
     */
    public function testWriteWithFormatter()
    {
        $event = array(
            'message' => 'tottakai',
            'priority' => Logger::ERR
        );

        $writer = new SyslogWriter();
        $formatter = new SimpleFormatter('%message% (this is a test)');
        $writer->setFormatter($formatter);

        $writer->write($event);
    }

    /**
     * @group Laminas-534
     */
    public function testPassApplicationNameViaConstructor()
    {
        $writer   = new CustomSyslogWriter(array('application' => 'test_app'));
        $this->assertEquals('test_app', $writer->getApplicationName());
    }

    public function testConstructWithOptions()
    {
        $formatter = new \Laminas\Log\Formatter\Simple();
        $filter    = new \Laminas\Log\Filter\Mock();
        $writer = new CustomSyslogWriter(array(
                'filters'   => $filter,
                'formatter' => $formatter,
                'application'  => 'test_app',
        ));
        $this->assertEquals('test_app', $writer->getApplicationName());
        $this->assertAttributeEquals($formatter, 'formatter', $writer);

        $filters = self::readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    public function testDefaultFormatter()
    {
        $writer   = new CustomSyslogWriter(array('application' => 'test_app'));
        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\Simple', 'formatter', $writer);
    }
}
