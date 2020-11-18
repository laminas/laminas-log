<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\ZendMonitor;
use PHPUnit\Framework\TestCase;

class ZendMonitorTest extends TestCase
{
    /**
     * @group Laminas-10081
     * @doesNotPerformAssertions
     */
    public function testWrite(): void
    {
        $writer = new ZendMonitor();
        $writer->write([
            'message' => 'my mess',
            'priority' => 1
        ]);
    }

    public function testIsEnabled(): void
    {
        $writer = new ZendMonitor();
        $this->assertIsBool($writer->isEnabled());
    }
}
