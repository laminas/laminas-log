<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Interop\Container\ContainerInterface;
use Laminas\Log\Processor\ProcessorInterface;
use Laminas\Log\ProcessorPluginManager;
use Laminas\Log\ProcessorPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

class ProcessorPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ProcessorPluginManagerFactory();

        $processors = $factory($container, ProcessorPluginManagerFactory::class);
        $this->assertInstanceOf(ProcessorPluginManager::class, $processors);

        if (method_exists($processors, 'configure')) {
            // laminas-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $processors);
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $processors->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $processor = $this->prophesize(ProcessorInterface::class)->reveal();

        $factory = new ProcessorPluginManagerFactory();
        $processors = $factory($container, ProcessorPluginManagerFactory::class, [
            'services' => [
                'test' => $processor,
            ],
        ]);
        $this->assertSame($processor, $processors->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $processor = $this->prophesize(ProcessorInterface::class)->reveal();

        $factory = new ProcessorPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $processor,
            ],
        ]);

        $processors = $factory->createService($container->reveal());
        $this->assertSame($processor, $processors->get('test'));
    }
}
