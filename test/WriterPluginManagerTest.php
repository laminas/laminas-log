<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class WriterPluginManagerTest extends TestCase
{
    /**
     * @var WriterPluginManager
     */
    protected $plugins;

    protected function setUp(): void
    {
        $this->plugins = new WriterPluginManager(new ServiceManager());
    }

    public function testInvokableClassFirephp(): void
    {
        $firephp = $this->plugins->get('firephp');
        $this->assertInstanceOf('Laminas\Log\Writer\Firephp', $firephp);
    }
}
