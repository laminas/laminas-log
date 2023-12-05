<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer\Factory;

use Laminas\Log\Writer\Factory\WriterFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasTest\Log\Writer\TestAsset\InvokableObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class WriterFactoryTest extends TestCase
{
    use ProphecyTrait;

    protected function createServiceManagerMock()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);
        return $container;
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::setCreationOptions
     */
    public function testSetCreationOptions(): void
    {
        // Arrange
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);
        $container = $container->reveal();

        $pluginManager = $this->prophesize(AbstractPluginManager::class);
        $pluginManager->getServiceLocator()->willReturn($container);
        $pluginManager = $pluginManager->reveal();

        $factory = new WriterFactory();
        $factory->setCreationOptions(['foo' => 'bar']);

        // Act
        $object = $factory->createService($pluginManager, InvokableObject::class, InvokableObject::class);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__construct
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::createService
     */
    public function testCreateServiceWithoutCreationOptions(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();

        $pluginManager = $this->prophesize(AbstractPluginManager::class);
        $pluginManager->getServiceLocator()->willReturn($container->reveal());
        $pluginManager = $pluginManager->reveal();

        $factory = new WriterFactory();

        // Act
        $object = $factory->createService($pluginManager, InvokableObject::class, InvokableObject::class);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__construct
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::createService
     */
    public function testCreateServiceWithCreationOptions(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();

        $pluginManager = $this->prophesize(AbstractPluginManager::class);
        $pluginManager->getServiceLocator()->willReturn($container->reveal());
        $pluginManager = $pluginManager->reveal();

        $factory = new WriterFactory(['foo' => 'bar']);

        // Act
        $object = $factory->createService($pluginManager, InvokableObject::class, InvokableObject::class);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__construct
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::createService
     */
    public function testCreateServiceWithValidRequestName(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();

        $pluginManager = $this->prophesize(AbstractPluginManager::class);
        $pluginManager->getServiceLocator()->willReturn($container->reveal());
        $pluginManager = $pluginManager->reveal();

        $factory = new WriterFactory();

        // Act
        $object = $factory->createService($pluginManager, 'invalid', InvokableObject::class);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__construct
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::createService
     */
    public function testCreateServiceInvalidNames(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();

        $pluginManager = $this->prophesize(AbstractPluginManager::class);
        $pluginManager->getServiceLocator()->willReturn($container->reveal());
        $pluginManager = $pluginManager->reveal();

        $factory = new WriterFactory();

        // Assert
        $this->expectException(InvalidServiceException::class);
        $this->expectExceptionMessage('WriterFactory requires that the requested name is provided');

        // Act
        $object = $factory->createService($pluginManager, 'invalid', 'invalid');
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithoutOptions(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();
        $factory   = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, []);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithInvalidFilterManagerAsString(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();
        $factory   = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, [
            'filter_manager' => 'my_manager',
        ]);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([
            'filter_manager' => null,
        ], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithValidFilterManagerAsString(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();
        $container->has('LogFormatterManager')->willReturn(false);
        $container->has('my_manager')->willReturn(true);
        $container->get('my_manager')->willReturn(123);

        $factory = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, [
            'filter_manager' => 'my_manager',
        ]);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([
            'filter_manager' => 123,
        ], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithoutFilterManager(): void
    {
        // Arrange
        $container = $this->createServiceManagerMock();
        $container->has('LogFilterManager')->willReturn(true);
        $container->get('LogFilterManager')->willReturn(123);
        $container->has('LogFormatterManager')->willReturn(false);

        $factory = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, []);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([
            'filter_manager' => 123,
        ], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithInvalidFormatterManagerAsString(): void
    {
        // Arrange
        $container = $this->prophesize(ContainerInterface::class);
        $factory   = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, [
            'formatter_manager' => 'my_manager',
        ]);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([
            'formatter_manager' => null,
        ], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithValidFormatterManagerAsString(): void
    {
        // Arrange
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('LogFilterManager')->willReturn(false);
        $container->get('my_manager')->willReturn(123);

        $factory = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, [
            'formatter_manager' => 'my_manager',
        ]);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([
            'formatter_manager' => 123,
        ], $object->options);
    }

    /**
     * @covers \Laminas\Log\Writer\Factory\WriterFactory::__invoke
     */
    public function testInvokeWithoutFormatterManager(): void
    {
        // Arrange
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('LogFilterManager')->willReturn(true);
        $container->get('LogFilterManager')->willReturn(null);
        $container->has('LogFormatterManager')->willReturn(true);
        $container->get('LogFormatterManager')->willReturn(123);

        $factory = new WriterFactory();

        // Act
        $object = $factory($container->reveal(), InvokableObject::class, []);

        // Assert
        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals([
            'filter_manager'    => null,
            'formatter_manager' => 123,
        ], $object->options);
    }
}
