<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Interop\Container\ContainerInterface;
use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\FormatterPluginManager;
use Laminas\Log\FormatterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

class FormatterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new FormatterPluginManagerFactory();

        $formatters = $factory($container, FormatterPluginManagerFactory::class);
        $this->assertInstanceOf(FormatterPluginManager::class, $formatters);

        if (method_exists($formatters, 'configure')) {
            // laminas-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $formatters);
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $formatters->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
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

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $formatter = $this->prophesize(FormatterInterface::class)->reveal();

        $factory = new FormatterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $formatter,
            ],
        ]);

        $formatters = $factory->createService($container->reveal());
        $this->assertSame($formatter, $formatters->get('test'));
    }
}
