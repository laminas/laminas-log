<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use ReflectionProperty;

trait ServicesNotSharedByDefaultTrait
{
    public function testServicesShouldNotBeSharedByDefault(): void
    {
        $plugins = $this->getPluginManager();

        $r = method_exists($plugins, 'configure')
            ? new ReflectionProperty($plugins, 'sharedByDefault')
            : new ReflectionProperty($plugins, 'shareByDefault');
        $r->setAccessible(true);
        $this->assertFalse($r->getValue($plugins));
    }
}
