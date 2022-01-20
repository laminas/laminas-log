<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Closure;
use Interop\Container\ContainerInterface;
use Laminas\Log\Writer\WriterInterface;
use Laminas\Log\WriterPluginManager;
use Laminas\Log\WriterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class WriterPluginManagerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory   = new WriterPluginManagerFactory();

        $writers = $factory($container, WriterPluginManagerFactory::class);
        $this->assertInstanceOf(WriterPluginManager::class, $writers);

        $creationContext = Closure::bind(fn() => $this->creationContext, $writers, WriterPluginManager::class)();
        $this->assertSame($container, $creationContext);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $writer    = $this->prophesize(WriterInterface::class)->reveal();

        $factory = new WriterPluginManagerFactory();
        $writers = $factory($container, WriterPluginManagerFactory::class, [
            'services' => [
                'test' => $writer,
            ],
        ]);
        $this->assertSame($writer, $writers->get('test'));
    }

    public function testConfiguresWriterServicesWhenFound(): void
    {
        $writer = $this->prophesize(WriterInterface::class)->reveal();
        $config = [
            'log_writers' => [
                'aliases'   => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => static fn($container) => $writer,
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new WriterPluginManagerFactory();
        $writers = $factory($container->reveal(), 'WriterManager');

        $this->assertInstanceOf(WriterPluginManager::class, $writers);
        $this->assertTrue($writers->has('test'));
        $this->assertSame($writer, $writers->get('test'));
        $this->assertTrue($writers->has('test-too'));
        $this->assertSame($writer, $writers->get('test-too'));
    }

    public function testDoesNotConfigureWriterServicesWhenServiceListenerPresent(): void
    {
        $writer = $this->prophesize(WriterInterface::class)->reveal();
        $config = [
            'log_writers' => [
                'aliases'   => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => static fn($container) => $writer,
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $factory = new WriterPluginManagerFactory();
        $writers = $factory($container->reveal(), 'WriterManager');

        $this->assertInstanceOf(WriterPluginManager::class, $writers);
        $this->assertFalse($writers->has('test'));
        $this->assertFalse($writers->has('test-too'));
    }

    public function testDoesNotConfigureWriterServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new WriterPluginManagerFactory();
        $writers = $factory($container->reveal(), 'WriterManager');

        $this->assertInstanceOf(WriterPluginManager::class, $writers);
    }

    public function testDoesNotConfigureWriterServicesWhenConfigServiceDoesNotContainWritersConfig(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);

        $factory = new WriterPluginManagerFactory();
        $writers = $factory($container->reveal(), 'WriterManager');

        $this->assertInstanceOf(WriterPluginManager::class, $writers);
        $this->assertFalse($writers->has('foo'));
    }
}
