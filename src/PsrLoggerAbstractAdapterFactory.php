<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * PSR Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class PsrLoggerAbstractAdapterFactory implements AbstractFactoryInterface
{
    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey = 'psr_log';

    private LoggerAbstractServiceFactory $loggerAbstractServiceFactory;

    public function __construct()
    {
        $this->loggerAbstractServiceFactory = new LoggerAbstractServiceFactory($this->configKey);
    }

    /**
     * @param string $requestedName
     * @return PsrLoggerAdapter
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $loggerFactory = $this->loggerAbstractServiceFactory;
        $logger        = $loggerFactory($container, $requestedName);

        return new PsrLoggerAdapter($logger);
    }

    /**
     * Determine if we can create a service with name
     *
     * @param string $name
     * @param string $requestedName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($serviceLocator, $name);
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param string $requestedName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->loggerAbstractServiceFactory->canCreate($container, $requestedName);
    }

    /**
     * Create service with name
     *
     * @param string $name
     * @param string $requestedName
     * @return PsrLoggerAdapter
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }
}
