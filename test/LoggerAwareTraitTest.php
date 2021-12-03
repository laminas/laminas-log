<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Laminas\Log\Logger;
use Laminas\Log\LoggerAwareTrait;
use PHPUnit\Framework\TestCase;

class LoggerAwareTraitTest extends TestCase
{
    public function testSetLogger(): void
    {
        $object = $this->getObjectForTrait(LoggerAwareTrait::class);

        $this->assertNull($object->getLogger());

        $logger = new Logger();

        $object->setLogger($logger);

        $this->assertSame($logger, $object->getLogger());
    }
}
