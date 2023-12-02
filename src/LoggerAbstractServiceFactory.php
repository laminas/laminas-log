<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class LoggerAbstractServiceFactory extends LoggerServiceFactory implements AbstractFactoryInterface
{
    /** @var array */
    protected $config;

    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey;

    public function __construct(string $configKey = 'log')
    {
        $this->configKey = $configKey;
    }

    /**
     * @param string $requestedName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container);
        if (empty($config)) {
            return false;
        }

        return isset($config[$requestedName]);
    }

    /**
     * @param string $name
     * @param string $requestedName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($serviceLocator, $requestedName);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $this->getConfig($container);
        $config = $config[$requestedName];

        $this->processConfig($config, $container);

        return new Logger($config);
    }

    /**
     * @param string $name
     * @param string $requestedName
     * @return Logger
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }

    /**
     * Retrieve configuration for loggers, if any
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConfig(ContainerInterface $services)
    {
        if (isset($this->config)) {
            return $this->config;
        }

        if (! $services->has('config')) {
            $this->config = [];

            return $this->config;
        }

        $config = $services->get('config');
        if (! isset($config[$this->configKey])) {
            $this->config = [];

            return $this->config;
        }

        $this->config = $config[$this->configKey];

        return $this->config;
    }
}
