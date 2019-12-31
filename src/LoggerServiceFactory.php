<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

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
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        // Configure the logger
        $config = $container->get('config');
        $logConfig = isset($config['log']) ? $config['log'] : [];
        return new Logger($logConfig);
    }

    /**
     * Factory for laminas-servicemanager v2.
     *
     * Proxies to `__invoke()`.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Logger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Logger::class);
    }
}
