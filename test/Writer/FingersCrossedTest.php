<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Closure;
use Laminas\Log\Filter\Priority;
use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\Writer\FingersCrossed as FingersCrossedWriter;
use Laminas\Log\Writer\Mock as MockWriter;
use Laminas\Log\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;

use function count;

class FingersCrossedTest extends TestCase
{
    public function testBuffering(): void
    {
        $wrappedWriter = new MockWriter();
        $writer        = new FingersCrossedWriter($wrappedWriter, 2);

        $writer->write(['priority' => 3, 'message' => 'foo']);

        $this->assertSame(count($wrappedWriter->events), 0);
    }

    public function testFlushing(): void
    {
        $wrappedWriter = new MockWriter();
        $writer        = new FingersCrossedWriter($wrappedWriter, 2);

        $writer->write(['priority' => 3, 'message' => 'foo']);
        $writer->write(['priority' => 1, 'message' => 'bar']);

        $this->assertSame(count($wrappedWriter->events), 2);
    }

    public function testAfterFlushing(): void
    {
        $wrappedWriter = new MockWriter();
        $writer        = new FingersCrossedWriter($wrappedWriter, 2);

        $writer->write(['priority' => 3, 'message' => 'foo']);
        $writer->write(['priority' => 1, 'message' => 'bar']);
        $writer->write(['priority' => 3, 'message' => 'bar']);

        $this->assertSame(count($wrappedWriter->events), 3);
    }

    public function setWriterByName()
    {
        $writer = new class ('mock') extends FingersCrossedWriter {
            public function getWriter(): WriterInterface
            {
                return $this->writer;
            }
        };
        $this->assertInstanceOf(MockWriter::class, $writer->getWriter());
    }

    public function testConstructorOptions(): void
    {
        $options = ['writer' => 'mock', 'priority' => 3];
        $writer  = new class ($options) extends FingersCrossedWriter {
            public function getWriter(): WriterInterface
            {
                return $this->writer;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };
        $this->assertInstanceOf(MockWriter::class, $writer->getWriter());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(Priority::class, $filters[0]);
        $priority = Closure::bind(fn() => $this->priority, $filters[0], Priority::class)();
        $this->assertEquals(3, $priority);
    }

    public function testFormattingIsNotSupported(): void
    {
        $options = ['writer' => 'mock', 'priority' => 3];
        $writer  = new class ($options) extends FingersCrossedWriter {
            public function getFormatter()
            {
                return $this->formatter;
            }
        };

        $writer->setFormatter($this->createMock(FormatterInterface::class));
        $this->assertEmpty($writer->getFormatter());
    }
}
