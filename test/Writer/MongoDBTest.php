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

        $mongoClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? 'Mongo' : 'MongoClient';

        $this->mongo = $this->getMockBuilder($mongoClass)
            ->disableOriginalConstructor()
            ->setMethods(['selectCollection'])
            ->getMock();

        $this->mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->mongo->expects($this->any())
            ->method('selectCollection')
            ->with($this->database, $this->collection)
            ->will($this->returnValue($this->mongoCollection));
    }

    public function testFormattingIsNotSupported()
    {
        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection);

        $writer->setFormatter($this->getMock('Laminas\Log\Formatter\FormatterInterface'));
        $this->assertAttributeEmpty('formatter', $writer);
    }

    public function testWriteWithDefaultSaveOptions()
    {
        $event = ['message'=> 'foo', 'priority' => 42];

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($event, []);

        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection);

        $writer->write($event);
    }

    public function testWriteWithCustomSaveOptions()
    {
        $event = ['message' => 'foo', 'priority' => 42];
        $saveOptions = ['safe' => false, 'fsync' => false, 'timeout' => 100];

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($event, $saveOptions);

        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection, $saveOptions);

        $writer->write($event);
    }

    public function testWriteConvertsDateTimeToMongoDate()
    {
        $date = new DateTime();
        $event = ['timestamp'=> $date];

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($this->contains(new MongoDate($date->getTimestamp()), false));

        $writer = new MongoDBWriter($this->mongo, $this->database, $this->collection);

        $writer->write($event);
    }
}
