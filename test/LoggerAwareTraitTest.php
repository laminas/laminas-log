<?php

namespace LaminasTest\Log;

use Laminas\Log\Logger;
use PHPUnit\Framework\TestCase;

class LoggerAwareTraitTest extends TestCase
{
    public function testSetLogger(): void
    {
        $object = $this->getObjectForTrait(\Laminas\Log\LoggerAwareTrait::class);

        $this->assertNull($object->getLogger());

        $logger = new Logger;

        $object->setLogger($logger);

        $this->assertSame($logger, $object->getLogger());
    }
}
