<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Filter\Regex as RegexFilter;
use Laminas\Log\FilterPluginManager;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\FormatterPluginManager;
use Laminas\Log\Writer\FilterPluginManager as LegacyFilterPluginManager;
use Laminas\Log\Writer\FormatterPluginManager as LegacyFormatterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Log\TestAsset\ConcreteWriter;
use LaminasTest\Log\TestAsset\ErrorGeneratingWriter;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class AbstractTest extends TestCase
{
    protected $writer;

    protected function setUp()
    {
        $this->writer = new ConcreteWriter();
    }

    public function testSetSimpleFormatterByName()
    {
        $instance = $this->writer->setFormatter('simple');
        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\Simple', 'formatter', $instance);
    }

    public function testAddFilter()
    {
        $this->writer->addFilter(1);
        $this->writer->addFilter(new RegexFilter('/mess/'));
        $this->expectException('Laminas\Log\Exception\InvalidArgumentException');
        $this->writer->addFilter(new \stdClass());
    }

    public function testAddMockFilterByName()
    {
        $instance = $this->writer->addFilter('mock');
        $this->assertInstanceOf('LaminasTest\Log\TestAsset\ConcreteWriter', $instance);
    }

    public function testAddRegexFilterWithParamsByName()
    {
        $instance = $this->writer->addFilter('regex', [ 'regex' => '/mess/' ]);
        $this->assertInstanceOf('LaminasTest\Log\TestAsset\ConcreteWriter', $instance);
    }

    /**
     * @group Laminas-8953
     */
    public function testFluentInterface()
    {
        $instance = $this->writer->addFilter(1)
                                  ->setFormatter(new SimpleFormatter());

        $this->assertInstanceOf('LaminasTest\Log\TestAsset\ConcreteWriter', $instance);
    }

    public function testConvertErrorsToException()
    {
        $writer = new ErrorGeneratingWriter();
        $this->expectException('Laminas\Log\Exception\RuntimeException');
        $writer->write(['message' => 'test']);

        $writer->setConvertWriteErrorsToExceptions(false);
        $this->expectException('PHPUnit_Framework_Error_Warning');
        $writer->write(['message' => 'test']);
    }

    public function testConstructorWithOptions()
    {
        $options = ['filters' => [
                             [
                                 'name' => 'mock',
                             ],
                             [
                                 'name' => 'priority',
                                 'options' => [
                                     'priority' => 3,
                                 ],
                             ],
                         ],
                        'formatter' => [
                             'name' => 'base',
                         ],
                    ];

        $writer = new ConcreteWriter($options);

        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\Base', 'formatter', $writer);

        $filters = $this->readAttribute($writer, 'filters');
        $this->assertCount(2, $filters);

        $this->assertInstanceOf('Laminas\Log\Filter\Priority', $filters[1]);
        $this->assertEquals(3, $this->readAttribute($filters[1], 'priority'));
    }

    public function testConstructorWithPriorityFilter()
    {
        // Accept an int as a PriorityFilter
        $writer = new ConcreteWriter(['filters' => 3]);
        $filters = $this->readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertInstanceOf('Laminas\Log\Filter\Priority', $filters[0]);
        $this->assertEquals(3, $this->readAttribute($filters[0], 'priority'));

        // Accept an int in an array of filters as a PriorityFilter
        $options = ['filters' => [3, ['name' => 'mock']]];

        $writer = new ConcreteWriter($options);
        $filters = $this->readAttribute($writer, 'filters');
        $this->assertCount(2, $filters);
        $this->assertInstanceOf('Laminas\Log\Filter\Priority', $filters[0]);
        $this->assertEquals(3, $this->readAttribute($filters[0], 'priority'));
        $this->assertInstanceOf('Laminas\Log\Filter\Mock', $filters[1]);
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithFormatterManager()
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
     * @expectedException Laminas\Log\Exception\InvalidArgumentException
     * @expectedExceptionMessage Writer plugin manager must extend Laminas\Log\FormatterPluginManager; received integer
     */
    public function testConstructorWithInvalidFormatterManager()
    {
        // Arrange
        // There is nothing to arrange.

        // Act
        $writer = new ConcreteWriter([
            'formatter_manager' => 123,
        ]);

        // Assert
        // No assert needed, expecting an exception.
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithLegacyFormatterManager()
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
    public function testConstructorWithFilterManager()
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
     * @expectedException Laminas\Log\Exception\InvalidArgumentException
     * @expectedExceptionMessage Writer plugin manager must extend Laminas\Log\FilterPluginManager; received integer
     */
    public function testConstructorWithInvalidFilterManager()
    {
        // Arrange
        // There is nothing to arrange.

        // Act
        $writer = new ConcreteWriter([
            'filter_manager' => 123,
        ]);

        // Assert
        // Nothing to assert, expecting an exception.
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::__construct
     */
    public function testConstructorWithLegacyFilterManager()
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
    public function testFormatterDefaultsToNull()
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
    public function testCanSetFormatter()
    {
        $formatter = new SimpleFormatter;
        $this->writer->setFormatter($formatter);

        $r = new ReflectionObject($this->writer);
        $m = $r->getMethod('getFormatter');
        $m->setAccessible(true);
        $this->assertSame($formatter, $m->invoke($this->writer));
    }

    /**
     * @covers \Laminas\Log\Writer\AbstractWriter::hasFormatter
     */
    public function testHasFormatter()
    {
        $r = new ReflectionObject($this->writer);
        $m = $r->getMethod('hasFormatter');
        $m->setAccessible(true);
        $this->assertFalse($m->invoke($this->writer));

        $this->writer->setFormatter(new SimpleFormatter);
        $this->assertTrue($m->invoke($this->writer));
    }
}
