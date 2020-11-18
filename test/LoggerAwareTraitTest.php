<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

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
