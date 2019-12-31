<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\ZendMonitor;

/**
 * @group      Laminas_Log
 */
class ZendMonitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group Laminas-10081
     */
    public function testWrite()
    {
        $writer = new ZendMonitor();
        $writer->write([
            'message' => 'my mess',
            'priority' => 1
        ]);
    }

    public function testIsEnabled()
    {
        $writer = new ZendMonitor();
        $this->assertInternalType('boolean', $writer->isEnabled());
    }
}
