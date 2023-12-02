<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Closure;
use Laminas\Log\Filter\FilterInterface;
use Laminas\Log\FilterPluginManager;
use Laminas\Log\FilterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class FilterPluginManagerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory   = new FilterPluginManagerFactory();

        $filters = $factory($container, FilterPluginManagerFactory::class);
        $this->assertInstanceOf(FilterPluginManager::class, $filters);

        $creationContext = Closure::bind(function () {
            return $this->creationContext;
        }, $filters, FilterPluginManager::class)();
        $this->assertSame($container, $creationContext);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerPSR(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $filter    = $this->prophesize(FilterInterface::class)->reveal();

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, FilterPluginManagerFactory::class, [
            'services' => [
                'test' => $filter,
            ],
        ]);
        $this->assertSame($filter, $filters->get('test'));
    }

    public function testConfiguresFilterServicesWhenFound(): void
    {
        $filter = $this->prophesize(FilterInterface::class)->reveal();
        $config = [
            'log_filters' => [
                'aliases'   => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($filter) {
                        return $filter;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
        $this->assertTrue($filters->has('test'));
        $this->assertSame($filter, $filters->get('test'));
        $this->assertTrue($filters->has('test-too'));
        $this->assertSame($filter, $filters->get('test-too'));
    }

    public function testDoesNotConfigureFilterServicesWhenServiceListenerPresent(): void
    {
        $filter = $this->prophesize(FilterInterface::class)->reveal();
        $config = [
            'log_filters' => [
                'aliases'   => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($filter) {
                        return $filter;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
        $this->assertFalse($filters->has('test'));
        $this->assertFalse($filters->has('test-too'));
    }

    public function testDoesNotConfigureFilterServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
    }

    public function testDoesNotConfigureFilterServicesWhenConfigServiceDoesNotContainFiltersConfig(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container->reveal(), 'FilterManager');

        $this->assertInstanceOf(FilterPluginManager::class, $filters);
        $this->assertFalse($filters->has('foo'));
    }
}
