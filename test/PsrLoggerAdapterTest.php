<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Laminas\Log\Logger;
use Laminas\Log\PsrLoggerAdapter;
use Laminas\Log\Writer\Mock as MockWriter;
use Psr\Log\LogLevel;
use Psr\Log\Test\LoggerInterfaceTest;

/**
 * @group Laminas_Log
 * @coversDefaultClass Laminas\Log\PsrLoggerAdapter
 * @covers ::<!public>
 */
class PsrLoggerAdapterTest extends LoggerInterfaceTest
{
    /**
     * @var array
     */
    protected $psrPriorityMap = [
        LogLevel::EMERGENCY => Logger::EMERG,
        LogLevel::ALERT     => Logger::ALERT,
        LogLevel::CRITICAL  => Logger::CRIT,
        LogLevel::ERROR     => Logger::ERR,
        LogLevel::WARNING   => Logger::WARN,
        LogLevel::NOTICE    => Logger::NOTICE,
        LogLevel::INFO      => Logger::INFO,
        LogLevel::DEBUG     => Logger::DEBUG,
    ];

    /**
     * Provides logger for LoggerInterface compat tests
     *
     * @return PsrLoggerAdapter
     */
    public function getLogger()
    {
        $this->mockWriter = new MockWriter;
        $logger           = new Logger;
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
    public function getLogs()
    {
        $prefixMap = array_flip($this->psrPriorityMap);
        return array_map(function ($event) use ($prefixMap) {
            $prefix  = $prefixMap[$event['priority']];
            $message = $prefix . ' ' . $event['message'];
            return $message;
        }, $this->mockWriter->events);
    }

    public function tearDown()
    {
        unset($this->mockWriter);
    }

    /**
     *
     * @covers ::__construct
     * @covers ::getLogger
     */
    public function testSetLogger()
    {
        $logger = new Logger;

        $adapter = new PsrLoggerAdapter($logger);
        $this->assertAttributeEquals($logger, 'logger', $adapter);

        $this->assertSame($logger, $adapter->getLogger($logger));
    }

    /**
     * @covers ::log
     * @dataProvider logLevelsToPriorityProvider
     */
    public function testPsrLogLevelsMapsToPriorities($logLevel, $priority)
    {
        $message = 'foo';
        $context = ['bar' => 'baz'];

        $logger = $this->getMock(Logger::class, ['log']);
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
    public function logLevelsToPriorityProvider()
    {
        $return = [];
        foreach ($this->psrPriorityMap as $level => $priority) {
            $return[] = [$level, $priority];
        }
        return $return;
    }
}
