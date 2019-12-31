<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Interop\Container\ContainerInterface;
use Laminas\Log\Writer\WriterInterface;
use Laminas\Log\WriterPluginManager;
use Laminas\Log\WriterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

class WriterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new WriterPluginManagerFactory();

        $writers = $factory($container, WriterPluginManagerFactory::class);
        $this->assertInstanceOf(WriterPluginManager::class, $writers);

        if (method_exists($writers, 'configure')) {
            // laminas-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $writers);
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $writers->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $writer = $this->prophesize(WriterInterface::class)->reveal();

        $factory = new WriterPluginManagerFactory();
        $writers = $factory($container, WriterPluginManagerFactory::class, [
            'services' => [
                'test' => $writer,
            ],
        ]);
        $this->assertSame($writer, $writers->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $writer = $this->prophesize(WriterInterface::class)->reveal();

        $factory = new WriterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $writer,
            ],
        ]);

        $writers = $factory->createService($container->reveal());
        $this->assertSame($writer, $writers->get('test'));
    }
}
