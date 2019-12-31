<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Laminas\Log\Logger;
use PHPUnit_Framework_TestCase as TestCase;

class LoggerAwareTraitTest extends TestCase
{
    public function testSetLogger()
    {
        $object = $this->getObjectForTrait('\Laminas\Log\LoggerAwareTrait');

        $this->assertAttributeEquals(null, 'logger', $object);

        $logger = new Logger;

        $object->setLogger($logger);

        $this->assertAttributeEquals($logger, 'logger', $object);
    }
}
