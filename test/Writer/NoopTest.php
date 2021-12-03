<?php

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
