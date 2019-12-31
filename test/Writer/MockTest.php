<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\Mock as MockWriter;

/**
 * @group      Laminas_Log
 */
class MockTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $writer = new MockWriter();
        $this->assertSame(array(), $writer->events);

        $fields = array('foo' => 'bar');
        $writer->write($fields);
        $this->assertSame(array($fields), $writer->events);
    }
}
