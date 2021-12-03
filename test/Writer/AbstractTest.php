<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Closure;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Exception\RuntimeException;
use Laminas\Log\Filter\Mock;
use Laminas\Log\Filter\Priority;
use Laminas\Log\Filter\Regex as RegexFilter;
use Laminas\Log\FilterPluginManager;
use Laminas\Log\Formatter\Base;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\FormatterPluginManager;
use Laminas\Log\Writer\FilterPluginManager as LegacyFilterPluginManager;
use Laminas\Log\Writer\FormatterPluginManager as LegacyFormatterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Log\TestAsset\ConcreteWriter;
use LaminasTest\Log\TestAsset\ErrorGeneratingWriter;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;

class AbstractTest extends TestCase
{
    protected $writer;

    protected function setUp(): void
    {
        $this->writer = new ConcreteWriter();
    }

    public function testSetSimpleFormatterByName(): void
    {
        $instance  = $this->writer->setFormatter('simple');
        $formatter = Closure::bind(function () {
            return $this->getFormatter();
        }, $instance, ConcreteWriter::class)();
        $this->assertInstanceOf(SimpleFormatter::class, $formatter);
    }

    public function testAddFilter(): void
    {
        $this->writer->addFilter(1);
        $this->writer->addFilter(new RegexFilter('/mess/'));
        $this->expectException(InvalidArgumentException::class);
        $this->writer->addFilter(new stdClass());
    }

    public function testAddMockFilterByName(): void
    {
        $instance = $this->writer->addFilter('mock');
        $this->assertInstanceOf(ConcreteWriter::class, $instance);
    }

    public function testAddRegexFilterWithParamsByName(): void
    {
        $instance = $this->writer->addFilter('regex', ['regex' => '/mess/']);
        $this->assertInstanceOf(ConcreteWriter::class, $instance);
    }

    /**
     * @group Laminas-8953
     */
    public function testFluentInterface(): void
    {
        $instance = $this->writer->addFilter(1)
                                  ->setFormatter(new SimpleFormatter());

        $this->assertInstanceOf(ConcreteWriter::class, $instance);
    }

    public function testConvertErrorsToException(): void
    {
        $writer = new ErrorGeneratingWriter();
        $this->expectException(RuntimeException::class);
        $writer->write(['message' => 'test']);

        $writer->setConvertWriteErrorsToExceptions(false);
        $this->expectWarning();
        $writer->write(['message' => 'test']);
    }

    public function testConstructorWithOptions(): void
    {
        $options = [
            'filters'   => [
                [
                    'name' => 'mock',
                ],
                [
                    'name'    => 'priority',
                    'options' => [
                        'priority' => 3,
                    ],
                ],
            ],
            'formatter' => [
                'name' => 'base',
            ],
        ];

        $writer = new class ($options) extends ConcreteWriter {
            public function getFormatter()
            {
                return $this->formatter;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };

        $this->assertInstanceOf(Base::class, $writer->getFormatter());

        $filters = $writer->getFilters();
        $this->assertCount(2, $filters);

        $priorityFilter = $filters[1];
        $this->assertInstanceOf(Priority::class, $priorityFilter);

        $priority = Closure::bind(function () {
            return $this->priority;
        }, $priorityFilter, Priority::class)();

        $this->assertEquals(3, $priority);
    }

    public function testConstructorWithPriorityFilter(): void
    {
        // Accept an int as a PriorityFilter
        $writer  = new class (['filters' => 3]) extends ConcreteWriter {
            public function getFilters(): array
            {
                return $this->filters;
            }
        };
        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(Priority::class, $filters[0]);
        $priority = Closure::bind(function () {
            return $this->priority;
        }, $filters[0], Priority::class)();
        $this->assertEquals(3, $priority);

        // Accept an int in an array of filters as a PriorityFilter
        $options = ['filters' => [3, ['name' => 'mock']]];

        $writer  = new class ($options) extends ConcreteWriter {
            public function getFilters(): array
            {
                return $this->filters;
            }
        };
        $filters = $writer->getFilters();
        $this->assertCount(2, $filters);
        $this->assertInstanceOf(Priority::class, $filters[0]);
        $priority = Closure::bind(function () {
            return $this->priority;
        }, $filters[0], Priority::class)();
        $this->assertEquals(3, $priority);
        $this->assertInstanceOf(Mock::class, $filters[1]);
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithFormatterManager(): void
    {
        // Arrange
        $pluginManager = new FormatterPluginManager(new ServiceManager());

        // Act
        $writer = new ConcreteWriter([
            'formatter_manager' => $pluginManager,
        ]);

        // Assert
        $this->assertSame($pluginManager, $writer->getFormatterPluginManager());
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithInvalidFormatterManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Writer plugin manager must extend Laminas\Log\FormatterPluginManager; received integer'
        );

        new ConcreteWriter([
            'formatter_manager' => 123,
        ]);
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithLegacyFormatterManager(): void
    {
        // Arrange
        $pluginManager = new LegacyFormatterPluginManager(new ServiceManager());

        // Act
        $writer = new ConcreteWriter([
            'formatter_manager' => $pluginManager,
        ]);

        // Assert
        $this->assertSame($pluginManager, $writer->getFormatterPluginManager());
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithFilterManager(): void
    {
        // Arrange
        $pluginManager = new FilterPluginManager(new ServiceManager());

        // Act
        $writer = new ConcreteWriter([
            'filter_manager' => $pluginManager,
        ]);

        // Assert
        $this->assertSame($pluginManager, $writer->getFilterPluginManager());
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithInvalidFilterManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Writer plugin manager must extend Laminas\Log\FilterPluginManager; received integer'
        );

        new ConcreteWriter([
            'filter_manager' => 123,
        ]);
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithLegacyFilterManager(): void
    {
        // Arrange
        $pluginManager = new LegacyFilterPluginManager(new ServiceManager());

        // Act
        $writer = new ConcreteWriter([
            'filter_manager' => $pluginManager,
        ]);

        // Assert
        $this->assertSame($pluginManager, $writer->getFilterPluginManager());
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::getFormatter
     */
    public function testFormatterDefaultsToNull(): void
    {
        $r = new ReflectionObject($this->writer);
        $m = $r->getMethod('getFormatter');
        $m->setAccessible(true);
        $this->assertNull($m->invoke($this->writer));
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::getFormatter
     * @covers \Laminas\Log\Writer\AbstractWriter::setFormatter
     */
    public function testCanSetFormatter(): void
    {
        $formatter = new SimpleFormatter();
        $this->writer->setFormatter($formatter);

        $r = new ReflectionObject($this->writer);
        $m = $r->getMethod('getFormatter');
        $m->setAccessible(true);
        $this->assertSame($formatter, $m->invoke($this->writer));
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::hasFormatter
     */
    public function testHasFormatter(): void
    {
        $r = new ReflectionObject($this->writer);
        $m = $r->getMethod('hasFormatter');
        $m->setAccessible(true);
        $this->assertFalse($m->invoke($this->writer));

        $this->writer->setFormatter(new SimpleFormatter());
        $this->assertTrue($m->invoke($this->writer));
    }
}
