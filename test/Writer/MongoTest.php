<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use DateTime;
use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\Writer\Mongo as MongoWriter;
use MongoDate;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function phpversion;
use function version_compare;

class MongoTest extends TestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('mongo')) {
            $this->markTestSkipped('The mongo PHP extension is not available');
        }

        $this->database   = 'laminas_test';
        $this->collection = 'logs';

        $mongoClass = version_compare(phpversion('mongo'), '1.3.0', '<') ? 'Mongo' : 'MongoClient';

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

    public function testFormattingIsNotSupported(): void
    {
        $writer = new MongoWriter($this->mongo, $this->database, $this->collection);

        $writer->setFormatter($this->createMock(FormatterInterface::class));
        $this->assertAttributeEmpty('formatter', $writer);
    }

    public function testWriteWithDefaultSaveOptions(): void
    {
        $event = ['message' => 'foo', 'priority' => 42];

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($event, []);

        $writer = new MongoWriter($this->mongo, $this->database, $this->collection);

        $writer->write($event);
    }

    public function testWriteWithCustomSaveOptions(): void
    {
        $event       = ['message' => 'foo', 'priority' => 42];
        $saveOptions = ['safe' => false, 'fsync' => false, 'timeout' => 100];

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($event, $saveOptions);

        $writer = new MongoWriter($this->mongo, $this->database, $this->collection, $saveOptions);

        $writer->write($event);
    }

    public function testWriteConvertsDateTimeToMongoDate(): void
    {
        $date  = new DateTime();
        $event = ['timestamp' => $date];

        $this->mongoCollection->expects($this->once())
            ->method('save')
            ->with($this->contains(new MongoDate($date->getTimestamp()), false));

        $writer = new MongoWriter($this->mongo, $this->database, $this->collection);

        $writer->write($event);
    }
}
