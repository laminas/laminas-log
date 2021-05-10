<?php

namespace LaminasTest\Log;

use Interop\Container\ContainerInterface;
use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\FormatterPluginManager;
use Laminas\Log\FormatterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FormatterPluginManagerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new FormatterPluginManagerFactory();

        $formatters = $factory($container, FormatterPluginManagerFactory::class);
        $this->assertInstanceOf(FormatterPluginManager::class, $formatters);

        $creationContext = \Closure::bind(function () {
            return $this->creationContext;
        }, $formatters, FormatterPluginManager::class)();
        $this->assertSame($container, $creationContext);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $formatter = $this->prophesize(FormatterInterface::class)->reveal();

        $factory = new FormatterPluginManagerFactory();
        $formatters = $factory($container, FormatterPluginManagerFactory::class, [
            'services' => [
                'test' => $formatter,
            ],
        ]);
        $this->assertSame($formatter, $formatters->get('test'));
    }

    public function testConfiguresFormatterServicesWhenFound(): void
    {
        $formatter = $this->prophesize(FormatterInterface::class)->reveal();
        $config = [
            'log_formatters' => [
                'aliases' => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($formatter) {
                        return $formatter;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $factory = new FormatterPluginManagerFactory();
        $formatters = $factory($container->reveal(), 'FormatterManager');

        $this->assertInstanceOf(FormatterPluginManager::class, $formatters);
        $this->assertTrue($formatters->has('test'));
        $this->assertSame($formatter, $formatters->get('test'));
        $this->assertTrue($formatters->has('test-too'));
        $this->assertSame($formatter, $formatters->get('test-too'));
    }

    public function testDoesNotConfigureFormatterServicesWhenServiceListenerPresent(): void
    {
        $formatter = $this->prophesize(FormatterInterface::class)->reveal();
        $config = [
            'log_formatters' => [
                'aliases' => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($formatter) {
                        return $formatter;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();

        $factory = new FormatterPluginManagerFactory();
        $formatters = $factory($container->reveal(), 'FormatterManager');

        $this->assertInstanceOf(FormatterPluginManager::class, $formatters);
        $this->assertFalse($formatters->has('test'));
        $this->assertFalse($formatters->has('test-too'));
    }

    public function testDoesNotConfigureFormatterServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new FormatterPluginManagerFactory();
        $formatters = $factory($container->reveal(), 'FormatterManager');

        $this->assertInstanceOf(FormatterPluginManager::class, $formatters);
    }

    public function testDoesNotConfigureFormatterServicesWhenConfigServiceDoesNotContainFormattersConfig(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);

        $factory = new FormatterPluginManagerFactory();
        $formatters = $factory($container->reveal(), 'FormatterManager');

        $this->assertInstanceOf(FormatterPluginManager::class, $formatters);
        $this->assertFalse($formatters->has('foo'));
    }
}
