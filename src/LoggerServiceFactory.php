<?php

declare(strict_types=1);

namespace Laminas\Log;

use ArrayAccess;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function get_class;
use function gettype;
use function is_array;
use function is_iterable;
use function is_object;
use function is_string;
use function iterator_to_array;

/**
 * Factory for logger instances.
 */
class LoggerServiceFactory implements FactoryInterface
{
    /**
     * Factory for laminas-servicemanager v3.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return Logger
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        // Configure the logger
        $config    = $container->get('config');
        $logConfig = $config['log'] ?? [];

        $this->processConfig($logConfig, $container);

        return new Logger($logConfig);
    }

    /**
     * Factory for laminas-servicemanager v2.
     *
     * Proxies to `__invoke()`.
     *
     * @return Logger
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Logger::class);
    }

    /**
     * Process and return the configuration from the container.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function processConfig(array &$config, ContainerInterface $services)
    {
        if (
            isset($config['writer_plugin_manager'])
            && is_string($config['writer_plugin_manager'])
            && $services->has($config['writer_plugin_manager'])
        ) {
            $config['writer_plugin_manager'] = $services->get($config['writer_plugin_manager']);
        }

        if (
            (! isset($config['writer_plugin_manager'])
                || ! $config['writer_plugin_manager'] instanceof AbstractPluginManager)
            && $services->has('LogWriterManager')
        ) {
            $config['writer_plugin_manager'] = $services->get('LogWriterManager');
        }

        if (
            isset($config['processor_plugin_manager'])
            && is_string($config['processor_plugin_manager'])
            && $services->has($config['processor_plugin_manager'])
        ) {
            $config['processor_plugin_manager'] = $services->get($config['processor_plugin_manager']);
        }

        if (
            (! isset($config['processor_plugin_manager'])
                || ! $config['processor_plugin_manager'] instanceof AbstractPluginManager)
            && $services->has('LogProcessorManager')
        ) {
            $config['processor_plugin_manager'] = $services->get('LogProcessorManager');
        }

        if (! isset($config['writers']) || ! is_iterable($config['writers'])) {
            return;
        }

        if (! is_array($config['writers'])) {
            $config['writers'] = iterator_to_array($config['writers']);
        }

        foreach ($config['writers'] as $index => $writerConfig) {
            if (! is_array($writerConfig) && ! $writerConfig instanceof ArrayAccess) {
                $type = is_object($writerConfig) ? get_class($writerConfig) : gettype($writerConfig);
                throw new InvalidArgumentException(
                    'config log.writers[] must contain array or ArrayAccess, ' . $type . ' provided'
                );
            }

            if (
                isset($writerConfig['name'])
                && ('db' === $writerConfig['name']
                    || Writer\Db::class === $writerConfig['name']
                    || 'laminaslogwriterdb' === $writerConfig['name']
                )
                && isset($writerConfig['options']['db'])
                && is_string($writerConfig['options']['db'])
                && $services->has($writerConfig['options']['db'])
            ) {
                // Retrieve the DB service from the service locator, and
                // inject it into the configuration.
                $db                                         = $services->get($writerConfig['options']['db']);
                $config['writers'][$index]['options']['db'] = $db;
                continue;
            }

            if (
                isset($writerConfig['name'])
                && ('mongo' === $writerConfig['name']
                    || Writer\Mongo::class === $writerConfig['name']
                    || 'laminaslogwritermongo' === $writerConfig['name']
                )
                && isset($writerConfig['options']['mongo'])
                && is_string($writerConfig['options']['mongo'])
                && $services->has($writerConfig['options']['mongo'])
            ) {
                // Retrieve the Mongo service from the service locator, and
                // inject it into the configuration.
                $mongoClient                                   = $services->get($writerConfig['options']['mongo']);
                $config['writers'][$index]['options']['mongo'] = $mongoClient;
                continue;
            }

            if (
                isset($writerConfig['name'])
                && ('mongodb' === $writerConfig['name']
                    || Writer\MongoDB::class === $writerConfig['name']
                    || 'laminaslogwritermongodb' === $writerConfig['name']
                )
                && isset($writerConfig['options']['manager'])
                && is_string($writerConfig['options']['manager'])
                && $services->has($writerConfig['options']['manager'])
            ) {
                // Retrieve the MongoDB Manager service from the service locator, and
                // inject it into the configuration.
                $manager                                         = $services->get($writerConfig['options']['manager']);
                $config['writers'][$index]['options']['manager'] = $manager;
                continue;
            }
        }
    }
}
