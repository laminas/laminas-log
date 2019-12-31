<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use DateTime;
use Laminas\Log\Writer\MongoDB as MongoDBWriter;
use MongoDB\BSON\UTCDatetime;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

/**
 * @group      Laminas_Log
 */
class MongoDBTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $collection;

    protected function setUp()
    {
        if (! extension_loaded('mongodb')) {
            $this->markTestSkipped('The mongodb PHP extension is not available');
        }

        $this->database = 'laminas_test';
        $this->collection = 'logs';

        $this->manager = new Manager('mongodb://localhost:27017');
    }

    protected function tearDown()
    {
        if (extension_loaded('mongodb')) {
            $this->manager->executeCommand($this->database, new Command(['dropDatabase' => 1]));
        }
    }

    public function testFormattingIsNotSupported()
    {
        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection);

        $writer->setFormatter($this->getMock('Laminas\Log\Formatter\FormatterInterface'));
        $this->assertAttributeEmpty('formatter', $writer);
    }

    public function testWriteWithDefaultSaveOptions()
    {
        $event = ['message'=> 'foo', 'priority' => 42];

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteWithCustomWriteConcern()
    {
        $event = ['message' => 'foo', 'priority' => 42];
        $writeConcern = ['journal' => false, 'wtimeout' => 100, 'wstring' => 1];

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection, $writeConcern);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteWithCustomWriteConcernInstance()
    {
        $event = ['message' => 'foo', 'priority' => 42];
        $writeConcern = new WriteConcern(1, 100, false);

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection, $writeConcern);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteWithoutCollectionNameWhenNamespaceIsGivenAsDatabase()
    {
        $event = ['message'=> 'foo', 'priority' => 42];

        $writer = new MongoDBWriter($this->manager, $this->database . '.' . $this->collection);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteConvertsDateTimeToMongoDate()
    {
        $date = new DateTime();
        $event = ['timestamp'=> $date];

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertInstanceOf(UTCDatetime::class, $entry->timestamp);
            $this->assertEquals($date, $entry->timestamp->toDateTime());
        }
    }
}
