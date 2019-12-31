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
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $_writer;

    protected function setUp()
    {
        $this->_writer = new ConcreteWriter();
    }

    /**
     * @group Laminas-6085
     */
    public function testSetFormatter()
    {
        $this->_writer->setFormatter(new SimpleFormatter());
        $this->setExpectedException('PHPUnit_Framework_Error');
        $this->_writer->setFormatter(new \StdClass());
    }

    public function testAddFilter()
    {
        $this->_writer->addFilter(1);
        $this->_writer->addFilter(new RegexFilter('/mess/'));
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException');
        $this->_writer->addFilter(new \StdClass());
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
}
