<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use DateTime;
use Laminas\Log\Writer\MongoDB as MongoDBWriter;
use MongoDate;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class MongoDBTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('The mongo PHP extension is not available');
        }

        $this->database = 'laminas_test';
        $this->collection = 'logs';

        $this->mongo = $this->getMockBuilder('Mongo')
            ->disableOriginalConstructor()
            ->setMethods(array('selectCollection'))
            ->getMock();

        $this->mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(array('save'))
            ->getMock();

        $this->mongo->expects($this->any())
            ->method('selectCollection')
            ->with($this->database, $this->collection)
            ->will($this->returnValue($this->mongoCollection));
    }

    /**
     * @expectedException Laminas\Log\Exception\InvalidArgumentException
     */
    public function testFormattingIsNotSupported()
    {
        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection);

        $writer->setFormatter($this->getMock('Laminas\Log\Formatter\FormatterInterface'));
    }

    public function testWriteWithDefaultSaveOptions()
    {
        $event = array('message'=> 'foo', 'priority' => 42);

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($event, array());

        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection);

        $writer->write($event);
    }

    public function testWriteWithCustomSaveOptions()
    {
        $event = array('message' => 'foo', 'priority' => 42);
        $saveOptions = array('safe' => false, 'fsync' => false, 'timeout' => 100);

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($event, $saveOptions);

        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection, $saveOptions);

        $writer->write($event);
    }

    public function testWriteConvertsDateTimeToMongoDate()
    {
        $date = new DateTime();
        $event = array('timestamp'=> $date);

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($this->contains(new MongoDate($date->getTimestamp()), false));

        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection);

        $writer->write($event);
    }
}
