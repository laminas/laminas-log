<?php

declare(strict_types=1);

namespace Laminas\Log;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for logger instances.
 */
class LoggerServiceFactory implements FactoryInterface
{
    /**
     * Factory for laminas-servicemanager v3.
     *
     * @param string $name
     * @param null|array $options
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        // Configure the logger
        $config    = $container->get('config');
        $logConfig = $config['log'] ?? [];
        return new Logger($logConfig);
    }

    /**
     * Factory for laminas-servicemanager v2.
     *
     * Proxies to `__invoke()`.
     *
     * @return Logger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Logger::class);
    }
}
