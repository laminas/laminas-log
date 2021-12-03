<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Closure;
use Laminas\Log\Filter\Mock as MockFilter;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Psr as PsrWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @coversDefaultClass \Laminas\Log\Writer\Psr
 * @covers ::<!public>
 */
class PsrTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructWithPsrLogger(): void
    {
        $psrLogger = $this->createMock(LoggerInterface::class);
        $writer    = new PsrWriter($psrLogger);
        $logger    = Closure::bind(function () {
            return $this->logger;
        }, $writer, PsrWriter::class)();
        $this->assertSame($psrLogger, $logger);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithOptions(): void
    {
        $psrLogger = $this->createMock(LoggerInterface::class);
        $formatter = new SimpleFormatter();
        $filter    = new MockFilter();
        $writer    = new class ([
            'filters'   => $filter,
            'formatter' => $formatter,
            'logger'    => $psrLogger,
        ]) extends PsrWriter {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }

            public function getFormatter()
            {
                return $this->formatter;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };

        $this->assertSame($psrLogger, $writer->getLogger());
        $this->assertSame($formatter, $writer->getFormatter());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    /**
     * @covers ::__construct
     */
    public function testFallbackLoggerIsNullLogger(): void
    {
        $writer = new class extends PsrWriter {
            public function getLogger(): LoggerInterface
            {
                return $this->logger;
            }
        };
        $this->assertInstanceOf(NullLogger::class, $writer->getLogger());
    }

    /**
     * @dataProvider priorityToLogLevelProvider
     */
    public function testWriteLogMapsLevelsProperly($priority, $logLevel): void
    {
        $message = 'foo';
        $extra   = ['bar' => 'baz'];

        $psrLogger = $this->createMock(LoggerInterface::class);
        $psrLogger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($logLevel),
                $this->equalTo($message),
                $this->equalTo($extra)
            );

        $writer = new PsrWriter($psrLogger);
        $logger = new Logger();
        $logger->addWriter($writer);

        $logger->log($priority, $message, $extra);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function priorityToLogLevelProvider()
    {
        return [
            'emergency' => [Logger::EMERG, LogLevel::EMERGENCY],
            'alert'     => [Logger::ALERT, LogLevel::ALERT],
            'critical'  => [Logger::CRIT, LogLevel::CRITICAL],
            'error'     => [Logger::ERR, LogLevel::ERROR],
            'warn'      => [Logger::WARN, LogLevel::WARNING],
            'notice'    => [Logger::NOTICE, LogLevel::NOTICE],
            'info'      => [Logger::INFO, LogLevel::INFO],
            'debug'     => [Logger::DEBUG, LogLevel::DEBUG],
        ];
    }
}
