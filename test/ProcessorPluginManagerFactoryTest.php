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
use Prophecy\PhpUnit\ProphecyTrait;

class ProcessorPluginManagerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ProcessorPluginManagerFactory();

        $processors = $factory($container, ProcessorPluginManagerFactory::class);
        $this->assertInstanceOf(ProcessorPluginManager::class, $processors);

        if (method_exists($processors, 'configure')) {
            // laminas-servicemanager v3
            $creationContext = \Closure::bind(function () {
                return $this->creationContext;
            }, $processors, ProcessorPluginManager::class)();
            $this->assertSame($container, $creationContext);
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $processors->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
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
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2(): void
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

    public function testConfiguresProcessorServicesWhenFound(): void
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

    public function testDoesNotConfigureProcessorServicesWhenServiceListenerPresent(): void
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

    public function testDoesNotConfigureProcessorServicesWhenConfigServiceNotPresent(): void
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

    public function testDoesNotConfigureProcessorServicesWhenConfigServiceDoesNotContainProcessorsConfig(): void
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
