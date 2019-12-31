<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\Noop as NoopWriter;

/**
 * @group      Laminas_Log
 */
class NoopTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $writer = new NoopWriter();
        $writer->write(array('message' => 'foo', 'priority' => 42));
    }
}
