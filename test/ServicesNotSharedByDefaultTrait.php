<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use ReflectionProperty;

use function method_exists;

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
