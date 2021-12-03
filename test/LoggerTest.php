<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use ArrayObject;
use ErrorException;
use Exception;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Exception\RuntimeException;
use Laminas\Log\Filter\Mock as MockFilter;
use Laminas\Log\Logger;
use Laminas\Log\Processor\Backtrace;
use Laminas\Log\Processor\RequestId;
use Laminas\Log\Writer\Mock as MockWriter;
use Laminas\Log\Writer\Noop;
use Laminas\Log\Writer\Stream as StreamWriter;
use Laminas\Log\WriterPluginManager;
use Laminas\Stdlib\SplPriorityQueue;
use Laminas\Validator\Digits as DigitsFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function class_exists;
use function count;
use function fclose;
use function fopen;
use function register_shutdown_function;
use function rewind;
use function set_exception_handler;
use function stream_get_contents;

use const E_USER_NOTICE;
use const PHP_VERSION_ID;

class LoggerTest extends TestCase
{
    /** @var Logger */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger();
    }

    public function testUsesWriterPluginManagerByDefault(): void
    {
        $this->assertInstanceOf(WriterPluginManager::class, $this->logger->getWriterPluginManager());
    }

    public function testPassingShortNameToPluginReturnsWriterByThatName(): void
    {
        $writer = $this->logger->writerPlugin('mock');
        $this->assertInstanceOf(MockWriter::class, $writer);
    }

    public function testPassWriterAsString(): void
    {
        $this->logger->addWriter('mock');
        $writers = $this->logger->getWriters();
        $this->assertInstanceOf(SplPriorityQueue::class, $writers);
    }

    public function testEmptyWriter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No log writer specified');
        $this->logger->log(Logger::INFO, 'test');
    }

    public function testSetWriters(): void
    {
        $writer1 = $this->logger->writerPlugin('mock');
        $writer2 = $this->logger->writerPlugin('null');
        $writers = new SplPriorityQueue();
        $writers->insert($writer1, 1);
        $writers->insert($writer2, 2);
        $this->logger->setWriters($writers);

        $writers = $this->logger->getWriters();
        $this->assertInstanceOf(SplPriorityQueue::class, $writers);
        $writer = $writers->extract();
        $this->assertInstanceOf(Noop::class, $writer);
        $writer = $writers->extract();
        $this->assertInstanceOf(MockWriter::class, $writer);
    }

    public function testAddWriterWithPriority(): void
    {
        $writer1 = $this->logger->writerPlugin('mock');
        $this->logger->addWriter($writer1, 1);
        $writer2 = $this->logger->writerPlugin('null');
        $this->logger->addWriter($writer2, 2);
        $writers = $this->logger->getWriters();

        $this->assertInstanceOf(SplPriorityQueue::class, $writers);
        $writer = $writers->extract();
        $this->assertInstanceOf(Noop::class, $writer);
        $writer = $writers->extract();
        $this->assertInstanceOf(MockWriter::class, $writer);
    }

    public function testAddWithSamePriority(): void
    {
        $writer1 = $this->logger->writerPlugin('mock');
        $this->logger->addWriter($writer1, 1);
        $writer2 = $this->logger->writerPlugin('null');
        $this->logger->addWriter($writer2, 1);
        $writers = $this->logger->getWriters();

        $this->assertInstanceOf(SplPriorityQueue::class, $writers);
        $writer = $writers->extract();
        $this->assertInstanceOf(MockWriter::class, $writer);
        $writer = $writers->extract();
        $this->assertInstanceOf(Noop::class, $writer);
    }

    public function testLogging(): void
    {
        $writer = new MockWriter();
        $this->logger->addWriter($writer);
        $this->logger->log(Logger::INFO, 'tottakai');

        $this->assertEquals(count($writer->events), 1);
        $this->assertStringContainsString('tottakai', $writer->events[0]['message']);
    }

    public function testLoggingArray(): void
    {
        $writer = new MockWriter();
        $this->logger->addWriter($writer);
        $this->logger->log(Logger::INFO, ['test']);

        $this->assertEquals(count($writer->events), 1);
        $this->assertStringContainsString('test', $writer->events[0]['message']);
    }

    public function testAddFilter(): void
    {
        $writer = new MockWriter();
        $filter = new MockFilter();
        $writer->addFilter($filter);
        $this->logger->addWriter($writer);
        $this->logger->log(Logger::INFO, ['test']);

        $this->assertEquals(count($filter->events), 1);
        $this->assertStringContainsString('test', $filter->events[0]['message']);
    }

    public function testAddFilterByName(): void
    {
        $writer = new MockWriter();
        $writer->addFilter('mock');
        $this->logger->addWriter($writer);
        $this->logger->log(Logger::INFO, ['test']);

        $this->assertEquals(count($writer->events), 1);
        $this->assertStringContainsString('test', $writer->events[0]['message']);
    }

    /**
     * provideTestFilters
     */
    public function provideTestFilters()
    {
        $data = [
            ['priority', ['priority' => Logger::INFO]],
            ['regex', ['regex' => '/[0-9]+/']],
        ];

        // Conditionally enabled until laminas-validator is forwards-compatible
        // with laminas-servicemanager v3.
        if (class_exists(DigitsFilter::class)) {
            $data[] = ['validator', ['validator' => new DigitsFilter()]];
        }

        return $data;
    }

    /**
     * @dataProvider provideTestFilters
     */
    public function testAddFilterByNameWithParams($filter, $options): void
    {
        $writer = new MockWriter();
        $writer->addFilter($filter, $options);
        $this->logger->addWriter($writer);

        $this->logger->log(Logger::INFO, '123');
        $this->assertEquals(count($writer->events), 1);
        $this->assertStringContainsString('123', $writer->events[0]['message']);
    }

    public static function provideAttributes()
    {
        return [
            [[]],
            [['user' => 'foo', 'ip' => '127.0.0.1']],
            [new ArrayObject(['id' => 42])],
        ];
    }

    /**
     * @dataProvider provideAttributes
     */
    public function testLoggingCustomAttributesForUserContext($extra): void
    {
        $writer = new MockWriter();
        $this->logger->addWriter($writer);
        $this->logger->log(Logger::ERR, 'tottakai', $extra);

        $this->assertEquals(count($writer->events), 1);
        $this->assertIsArray($writer->events[0]['extra']);
        $this->assertEquals(count($writer->events[0]['extra']), count($extra));
    }

    public static function provideInvalidArguments()
    {
        return [
            [new stdClass(), ['valid']],
            ['valid', null],
            ['valid', true],
            ['valid', 10],
            ['valid', 'invalid'],
            ['valid', new stdClass()],
        ];
    }

    /**
     * @dataProvider provideInvalidArguments
     */
    public function testPassingInvalidArgumentToLogRaisesException($message, $extra): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->log(Logger::ERR, $message, $extra);
    }

    public function testRegisterErrorHandler(): void
    {
        $writer = new MockWriter();
        $this->logger->addWriter($writer);

        $previous = Logger::registerErrorHandler($this->logger);
        $this->assertNotNull($previous);
        $this->assertNotFalse($previous);

        // check for single error handler instance
        $this->assertFalse(Logger::registerErrorHandler($this->logger));

        // generate a warning
        echo $test; // $test is not defined

        Logger::unregisterErrorHandler();

        if (PHP_VERSION_ID < 80000) {
            $this->assertEquals('Undefined variable: test', $writer->events[0]['message']);
        } else {
            $this->assertEquals('Undefined variable $test', $writer->events[0]['message']);
        }
    }

    public function testOptionsWithMock(): void
    {
        $options = [
            'writers' => [
                'first_writer' => [
                    'name' => 'mock',
                ],
            ],
        ];
        $logger  = new Logger($options);

        $writers = $logger->getWriters()->toArray();
        $this->assertCount(1, $writers);
        $this->assertInstanceOf(MockWriter::class, $writers[0]);
    }

    public function testOptionsWithWriterOptions(): void
    {
        $options = [
            'writers' => [
                [
                    'name'    => 'stream',
                    'options' => [
                        'stream'        => 'php://output',
                        'log_separator' => 'foo',
                    ],
                ],
            ],
        ];
        $logger  = new Logger($options);

        $writers = $logger->getWriters()->toArray();
        $this->assertCount(1, $writers);
        $this->assertInstanceOf(StreamWriter::class, $writers[0]);
        $this->assertEquals('foo', $writers[0]->getLogSeparator());
    }

    public function testOptionsWithMockAndProcessor(): void
    {
        $options    = [
            'writers'    => [
                'first_writer' => [
                    'name' => 'mock',
                ],
            ],
            'processors' => [
                'first_processor' => [
                    'name' => 'requestid',
                ],
            ],
        ];
        $logger     = new Logger($options);
        $processors = $logger->getProcessors()->toArray();
        $this->assertCount(1, $processors);
        $this->assertInstanceOf(RequestId::class, $processors[0]);
    }

    public function testAddProcessor(): void
    {
        $processor = new Backtrace();
        $this->logger->addProcessor($processor);

        $processors = $this->logger->getProcessors()->toArray();
        $this->assertEquals($processor, $processors[0]);
    }

    public function testAddProcessorByName(): void
    {
        $this->logger->addProcessor('backtrace');

        $processors = $this->logger->getProcessors()->toArray();
        $this->assertInstanceOf(Backtrace::class, $processors[0]);

        $writer = new MockWriter();
        $this->logger->addWriter($writer);
        $this->logger->log(Logger::ERR, 'foo');
    }

    public function testProcessorOutputAdded(): void
    {
        $processor = new Backtrace();
        $this->logger->addProcessor($processor);
        $writer = new MockWriter();
        $this->logger->addWriter($writer);

        $this->logger->log(Logger::ERR, 'foo');
        $this->assertEquals(__FILE__, $writer->events[0]['extra']['file']);
    }

    public function testExceptionHandler(): void
    {
        $writer = new MockWriter();
        $this->logger->addWriter($writer);

        $this->assertTrue(Logger::registerExceptionHandler($this->logger));

        // check for single error handler instance
        $this->assertFalse(Logger::registerExceptionHandler($this->logger));

        // get the internal exception handler
        $exceptionHandler = set_exception_handler(function ($e) {
        });
        set_exception_handler($exceptionHandler);

        // reset the exception handler
        Logger::unregisterExceptionHandler();

        // call the exception handler
        $exceptionHandler(new Exception('error', 200, new Exception('previos', 100)));
        $exceptionHandler(new ErrorException('user notice', 1000, E_USER_NOTICE, __FILE__, __LINE__));

        // check logged messages
        $expectedEvents = [
            ['priority' => Logger::ERR,    'message' => 'previos',     'file' => __FILE__],
            ['priority' => Logger::ERR,    'message' => 'error',       'file' => __FILE__],
            ['priority' => Logger::NOTICE, 'message' => 'user notice', 'file' => __FILE__],
        ];
        for ($i = 0; $i < count($expectedEvents); $i++) {
            $expectedEvent = $expectedEvents[$i];
            $event         = $writer->events[$i];

            $this->assertEquals($expectedEvent['priority'], $event['priority'], 'Unexpected priority');
            $this->assertEquals($expectedEvent['message'], $event['message'], 'Unexpected message');
            $this->assertEquals($expectedEvent['file'], $event['extra']['file'], 'Unexpected file');
        }
    }

    public function testLogExtraArrayKeyWithNonArrayValue(): void
    {
        $stream  = fopen("php://memory", "r+");
        $options = [
            'writers' => [
                [
                    'name'    => 'stream',
                    'options' => [
                        'stream' => $stream,
                    ],
                ],
            ],
        ];
        $logger  = new Logger($options);

        $this->assertInstanceOf(Logger::class, $logger->info('Hi', ['extra' => '']));
        fclose($stream);
    }

    /**
     * @group 5383
     */
    public function testErrorHandlerWithStreamWriter(): void
    {
        $options      = ['errorhandler' => true];
        $logger       = new Logger($options);
        $stream       = fopen('php://memory', 'w+');
        $streamWriter = new StreamWriter($stream);

        // error handler does not like this feature so turn it off
        $streamWriter->setConvertWriteErrorsToExceptions(false);
        $logger->addWriter($streamWriter);

        // we raise two notices - both should be logged
        echo $test;
        echo $second;

        rewind($stream);
        $contents = stream_get_contents($stream);
        $this->assertStringContainsString('test', $contents);
        $this->assertStringContainsString('second', $contents);
    }

    public function testRegisterFatalShutdownFunction(): void
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('PHP7: cannot test as code now raises E_ERROR');
        }

        $writer = new MockWriter();
        $this->logger->addWriter($writer);

        $result = Logger::registerFatalErrorShutdownFunction($this->logger);
        $this->assertTrue($result);

        // check for single error handler instance
        $this->assertFalse(Logger::registerFatalErrorShutdownFunction($this->logger));

        register_shutdown_function(function () use ($writer) {
            $this->assertEquals(
                'Call to undefined method LaminasTest\Log\LoggerTest::callToNonExistingMethod()',
                $writer->events[0]['message']
            );
        });

        // Temporarily hide errors, because we don't want the fatal error to fail the test
        @$this->callToNonExistingMethod();
    }

    /**
     * @group 6424
     */
    public function testRegisterFatalErrorShutdownFunctionHandlesCompileTimeErrors(): void
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('PHP7: cannot test as code now raises E_ERROR');
        }

        $writer = new MockWriter();
        $this->logger->addWriter($writer);

        $result = Logger::registerFatalErrorShutdownFunction($this->logger);
        $this->assertTrue($result);

        // check for single error handler instance
        $this->assertFalse(Logger::registerFatalErrorShutdownFunction($this->logger));

        register_shutdown_function(function () use ($writer) {
            $this->assertStringMatchesFormat(
                'syntax error%A',
                $writer->events[0]['message']
            );
        });

        // Temporarily hide errors, because we don't want the fatal error to fail the test
        @eval('this::code::is::invalid {}');
    }

    /**
     * @group Laminas-7238
     */
    public function testCatchExceptionNotValidPriority(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$priority must be an integer >= 0 and < 8; received -1');
        $writer = new MockWriter();
        $this->logger->addWriter($writer);
        $this->logger->log(-1, 'Foo');
    }
}
