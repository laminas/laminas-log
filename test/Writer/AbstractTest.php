<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Filter\Regex as RegexFilter;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use LaminasTest\Log\TestAsset\ConcreteWriter;
use LaminasTest\Log\TestAsset\ErrorGeneratingWriter;

/**
 * @group      Laminas_Log
 */
class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $_writer;

    protected function setUp()
    {
        $this->_writer = new ConcreteWriter();
    }

    public function testSetSimpleFormatterByName()
    {
        $instance = $this->_writer->setFormatter('simple');
        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\Simple', 'formatter', $instance);
    }

    public function testAddFilter()
    {
        $this->_writer->addFilter(1);
        $this->_writer->addFilter(new RegexFilter('/mess/'));
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException');
        $this->_writer->addFilter(new \stdClass());
    }

    public function testAddMockFilterByName()
    {
        $instance = $this->_writer->addFilter('mock');
        $this->assertTrue($instance instanceof ConcreteWriter);
    }

    public function testAddRegexFilterWithParamsByName()
    {
        $instance = $this->_writer->addFilter('regex', array( 'regex' => '/mess/' ));
        $this->assertTrue($instance instanceof ConcreteWriter);
    }

    /**
     * @group Laminas-8953
     */
    public function testFluentInterface()
    {
        $instance = $this->_writer->addFilter(1)
                                  ->setFormatter(new SimpleFormatter());

        $this->assertTrue($instance instanceof ConcreteWriter);
    }

    public function testConvertErrorsToException()
    {
        $writer = new ErrorGeneratingWriter();
        $this->setExpectedException('Laminas\Log\Exception\RuntimeException');
        $writer->write(array('message' => 'test'));

        $writer->setConvertWriteErrorsToExceptions(false);
        $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        $writer->write(array('message' => 'test'));
    }

    public function testConstructorWithOptions()
    {
        $options = array('filters' => array(
                             array(
                                 'name' => 'mock',
                             ),
                             array(
                                 'name' => 'priority',
                                 'options' => array(
                                     'priority' => 3,
                                 ),
                             ),
                         ),
                        'formatter' => array(
                             'name' => 'base',
                         ),
                    );

        $writer = new ConcreteWriter($options);

        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\Base', 'formatter', $writer);

        $filters = $this->readAttribute($writer, 'filters');
        $this->assertCount(2, $filters);

        $this->assertInstanceOf('Laminas\Log\Filter\Priority', $filters[1]);
        $this->assertEquals(3, $this->readAttribute($filters[1], 'priority'));
    }
}
