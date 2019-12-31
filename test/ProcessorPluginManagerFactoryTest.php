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
use PHPUnit\Framework\TestCase;

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

    public function testConfiguresProcessorServicesWhenFound()
    {
        $processor = $this->prophesize(ProcessorInterface::class)->reveal();
        $config = [
            'log_processors' => [
                'aliases' => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($processor) {
                        return $processor;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new ProcessorPluginManagerFactory();
        $processors = $factory($container->reveal(), 'ProcessorManager');

        $this->assertInstanceOf(ProcessorPluginManager::class, $processors);
        $this->assertTrue($processors->has('test'));
        $this->assertSame($processor, $processors->get('test'));
        $this->assertTrue($processors->has('test-too'));
        $this->assertSame($processor, $processors->get('test-too'));
    }

    public function testDoesNotConfigureProcessorServicesWhenServiceListenerPresent()
    {
        $processor = $this->prophesize(ProcessorInterface::class)->reveal();
        $config = [
            'log_processors' => [
                'aliases' => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($processor) {
                        return $processor;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $factory = new ProcessorPluginManagerFactory();
        $processors = $factory($container->reveal(), 'ProcessorManager');

        $this->assertInstanceOf(ProcessorPluginManager::class, $processors);
        $this->assertFalse($processors->has('test'));
        $this->assertFalse($processors->has('test-too'));
    }

    public function testDoesNotConfigureProcessorServicesWhenConfigServiceNotPresent()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new ProcessorPluginManagerFactory();
        $processors = $factory($container->reveal(), 'ProcessorManager');

        $this->assertInstanceOf(ProcessorPluginManager::class, $processors);
    }

    public function testDoesNotConfigureProcessorServicesWhenConfigServiceDoesNotContainProcessorsConfig()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);

        $factory = new ProcessorPluginManagerFactory();
        $processors = $factory($container->reveal(), 'ProcessorManager');

        $this->assertInstanceOf(ProcessorPluginManager::class, $processors);
        $this->assertFalse($processors->has('foo'));
    }
}
