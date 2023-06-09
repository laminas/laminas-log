<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use DateTime;
use Laminas\Log\Logger;
use Laminas\Log\PsrLoggerAdapter;
use Laminas\Log\Writer\Mock as MockWriter;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;

use function array_flip;
use function array_map;
use function fclose;
use function fopen;

/**
 * @coversDefaultClass \Laminas\Log\PsrLoggerAdapter
 * @covers ::<!public>
 */
class PsrLoggerAdapterTest extends TestCase
{
    /** @var array */
    protected array $psrPriorityMap = [
        LogLevel::EMERGENCY => Logger::EMERG,
        LogLevel::ALERT     => Logger::ALERT,
        LogLevel::CRITICAL  => Logger::CRIT,
        LogLevel::ERROR     => Logger::ERR,
        LogLevel::WARNING   => Logger::WARN,
        LogLevel::NOTICE    => Logger::NOTICE,
        LogLevel::INFO      => Logger::INFO,
        LogLevel::DEBUG     => Logger::DEBUG,
    ];

    private ?MockWriter $mockWriter = null;

    /**
     * Provides logger for LoggerInterface compat tests
     *
     * @return PsrLoggerAdapter
     */
    public function getLogger(): PsrLoggerInterface
    {
        $this->mockWriter = new MockWriter();
        $logger           = new Logger();
        $logger->addProcessor('psrplaceholder');
        $logger->addWriter($this->mockWriter);

        return new PsrLoggerAdapter($logger);
    }

    /**
     * This must return the log messages in order.
     *
     * The simple formatting of the messages is: "<LOG LEVEL> <MESSAGE>".
     *
     * Example ->error('Foo') would yield "error Foo".
     *
     * @return string[]
     */
    public function getLogs(): array
    {
        $prefixMap = array_flip($this->psrPriorityMap);

        return array_map(function ($event) use ($prefixMap) {
            $prefix = $prefixMap[$event['priority']];

            return $prefix . ' ' . $event['message'];
        }, $this->mockWriter->events);
    }

    protected function tearDown(): void
    {
        $this->mockWriter = null;
    }

    /**
     * @covers ::__construct
     * @covers ::getLogger
     */
    public function testSetLogger(): void
    {
        $logger = new Logger();

        $adapter = new PsrLoggerAdapter($logger);
        $this->assertSame($logger, $adapter->getLogger());
    }

    /**
     * @covers ::log
     * @dataProvider logLevelsToPriorityProvider
     */
    public function testPsrLogLevelsMapsToPriorities($logLevel, $priority): void
    {
        $message = 'foo';
        $context = ['bar' => 'baz'];

        $logger = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['log'])
            ->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($priority),
                $this->equalTo($message),
                $this->equalTo($context)
            );

        $adapter = new PsrLoggerAdapter($logger);
        $adapter->log($logLevel, $message, $context);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function logLevelsToPriorityProvider(): array
    {
        $return = [];
        foreach ($this->psrPriorityMap as $level => $priority) {
            $return[] = [$level, $priority];
        }
        return $return;
    }

    public function testThrowsOnInvalidLevel()
    {
        $logger = $this->getLogger();
        $this->expectException(InvalidArgumentException::class);
        $logger->log('invalid level', 'Foo');
    }

    public function testImplements()
    {
        $this->assertInstanceOf(PsrLoggerInterface::class, $this->getLogger());
    }

    /**
     * @dataProvider provideLevelsAndMessages
     */
    public function testLogsAtAllLevels($level, $message)
    {
        $logger = $this->getLogger();
        $logger->{$level}($message, ['user' => 'Bob']);
        $logger->log($level, $message, ['user' => 'Bob']);

        $expected = [
            $level . ' message of level ' . $level . ' with context: Bob',
            $level . ' message of level ' . $level . ' with context: Bob',
        ];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function provideLevelsAndMessages(): array
    {
        return [
            LogLevel::EMERGENCY => [LogLevel::EMERGENCY, 'message of level emergency with context: {user}'],
            LogLevel::ALERT     => [LogLevel::ALERT, 'message of level alert with context: {user}'],
            LogLevel::CRITICAL  => [LogLevel::CRITICAL, 'message of level critical with context: {user}'],
            LogLevel::ERROR     => [LogLevel::ERROR, 'message of level error with context: {user}'],
            LogLevel::WARNING   => [LogLevel::WARNING, 'message of level warning with context: {user}'],
            LogLevel::NOTICE    => [LogLevel::NOTICE, 'message of level notice with context: {user}'],
            LogLevel::INFO      => [LogLevel::INFO, 'message of level info with context: {user}'],
            LogLevel::DEBUG     => [LogLevel::DEBUG, 'message of level debug with context: {user}'],
        ];
    }

    public function testContextReplacement()
    {
        $logger = $this->getLogger();
        $logger->info('{Message {nothing} {user} {foo.bar} a}', ['user' => 'Bob', 'foo.bar' => 'Bar']);

        $expected = ['info {Message {nothing} Bob Bar a}'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testObjectCastToString()
    {
        $dummy = $this->createPartialMock(Psr\DummyTest::class, ['__toString']);
        $dummy->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('DUMMY'));

        $this->getLogger()->warning($dummy);

        $expected = ['warning DUMMY'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testContextCanContainAnything()
    {
        $closed = fopen('php://memory', 'r');
        fclose($closed);

        $context = [
            'bool'     => true,
            'null'     => null,
            'string'   => 'Foo',
            'int'      => 0,
            'float'    => 0.5,
            'nested'   => ['with object' => new Psr\DummyTest()],
            'object'   => new DateTime(),
            'resource' => fopen('php://memory', 'r'),
            'closed'   => $closed,
        ];

        $this->getLogger()->warning('Crazy context data', $context);

        $expected = ['warning Crazy context data'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues()
    {
        $logger = $this->getLogger();
        $logger->warning('Random message', ['exception' => 'oops']);
        $logger->critical('Uncaught Exception!', ['exception' => new LogicException('Fail')]);

        $expected = [
            'warning Random message',
            'critical Uncaught Exception!',
        ];
        $this->assertEquals($expected, $this->getLogs());
    }
}
