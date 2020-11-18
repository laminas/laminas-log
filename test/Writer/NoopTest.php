<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Writer\Noop as NoopWriter;
use PHPUnit\Framework\TestCase;

class NoopTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testWrite(): void
    {
        $writer = new NoopWriter();
        $writer->write(['message' => 'foo', 'priority' => 42]);
    }
}
