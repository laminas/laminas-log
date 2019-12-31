<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Logger;
use Laminas\Log\Writer\FingersCrossed as FingersCrossedWriter;
use Laminas\Log\Writer\Mock as MockWriter;

/**
 * @group      Laminas_Log
 */
class FingersCrossedTest extends \PHPUnit_Framework_TestCase
{
    public function testBuffering()
    {
        $wrappedWriter = new MockWriter();
        $writer = new FingersCrossedWriter($wrappedWriter, 2);

        $writer->write(array('priority' => 3, 'message' => 'foo'));

        $this->assertSame(count($wrappedWriter->events), 0);
    }

    public function testFlushing()
    {
        $wrappedWriter = new MockWriter();
        $writer = new FingersCrossedWriter($wrappedWriter, 2);

        $writer->write(array('priority' => 3, 'message' => 'foo'));
        $writer->write(array('priority' => 1, 'message' => 'bar'));

        $this->assertSame(count($wrappedWriter->events), 2);
    }

    public function testAfterFlushing()
    {
        $wrappedWriter = new MockWriter();
        $writer = new FingersCrossedWriter($wrappedWriter, 2);

        $writer->write(array('priority' => 3, 'message' => 'foo'));
        $writer->write(array('priority' => 1, 'message' => 'bar'));
        $writer->write(array('priority' => 3, 'message' => 'bar'));

        $this->assertSame(count($wrappedWriter->events), 3);
    }

    public function setWriterByName()
    {
        $writer = new FingersCrossedWriter('mock');
        $this->assertAttributeInstanceOf('Laminas\Log\Writer\Mock', 'writer', $writer);
    }

    public function testConstructorOptions()
    {
        $options = array('writer' => 'mock', 'priority' => 3);
        $writer = new FingersCrossedWriter($options);
        $this->assertAttributeInstanceOf('Laminas\Log\Writer\Mock', 'writer', $writer);

        $filters = $this->readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertInstanceOf('Laminas\Log\Filter\Priority', $filters[0]);
        $this->assertAttributeEquals(3, 'priority', $filters[0]);
    }

    public function testFormattingIsNotSupported()
    {
        $options = array('writer' => 'mock', 'priority' => 3);
        $writer = new FingersCrossedWriter($options);

        $writer->setFormatter($this->getMock('Laminas\Log\Formatter\FormatterInterface'));
        $this->assertAttributeEmpty('formatter', $writer);
    }
}
