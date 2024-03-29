<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use DateTime;
use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\Writer\MongoDB as MongoDBWriter;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function extension_loaded;
use function getenv;
use function sprintf;

class MongoDBTest extends TestCase
{
    /** @var Manager */
    protected $manager;

    /** @var string */
    protected $database;

    /** @var string */
    protected $collection;

    protected function setUp(): void
    {
        if (! extension_loaded('mongodb')) {
            $this->markTestSkipped('The mongodb PHP extension is not available');
        }

        $this->database   = 'laminas_test';
        $this->collection = 'logs';

        $this->manager = new Manager(sprintf(
            'mongodb://%s:%s',
            getenv('TESTS_LAMINAS_LOG_MONGODB_HOST'),
            getenv('TESTS_LAMINAS_LOG_MONGODB_PORT')
        ));
    }

    protected function tearDown(): void
    {
        if (extension_loaded('mongodb')) {
            $this->manager->executeCommand($this->database, new Command(['dropDatabase' => 1]));
        }
    }

    public function testFormattingIsNotSupported(): void
    {
        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection);

        $writer->setFormatter($this->createMock(FormatterInterface::class));

        $r = new ReflectionProperty($writer, 'formatter');
        $r->setAccessible(true);

        $this->assertEmpty($r->getValue($writer), 'Formatter property was not empty, but was expected to be');
    }

    public function testWriteWithDefaultSaveOptions(): void
    {
        $event = ['message' => 'foo', 'priority' => 42];

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteWithCustomWriteConcern(): void
    {
        $event        = ['message' => 'foo', 'priority' => 42];
        $writeConcern = ['journal' => false, 'wtimeout' => 100, 'wstring' => 1];

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection, $writeConcern);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteWithCustomWriteConcernInstance(): void
    {
        $event        = ['message' => 'foo', 'priority' => 42];
        $writeConcern = new WriteConcern(1, 100, false);

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection, $writeConcern);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteWithoutCollectionNameWhenNamespaceIsGivenAsDatabase(): void
    {
        $event = ['message' => 'foo', 'priority' => 42];

        $writer = new MongoDBWriter($this->manager, $this->database . '.' . $this->collection);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertEquals('foo', $entry->message);
            $this->assertEquals(42, $entry->priority);
        }
    }

    public function testWriteConvertsDateTimeToMongoDate(): void
    {
        $date  = new DateTime();
        $event = ['timestamp' => $date];

        $writer = new MongoDBWriter($this->manager, $this->database, $this->collection);

        $writer->write($event);

        $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, new Query([]));

        foreach ($cursor as $entry) {
            $this->assertInstanceOf(UTCDateTime::class, $entry->timestamp);
            $this->assertEquals($date->format('c'), $entry->timestamp->toDateTime()->format('c'));
        }
    }
}
