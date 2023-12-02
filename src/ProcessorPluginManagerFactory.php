<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;

use function is_array;

class ProcessorPluginManagerFactory implements FactoryInterface
{
    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return ProcessorPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        $pluginManager = new ProcessorPluginManager($container, $options ?: []);

        // If this is in a laminas-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return $pluginManager;
        }

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have log_processors configuration, nothing more to do
        if (! isset($config['log_processors']) || ! is_array($config['log_processors'])) {
            return $pluginManager;
        }

        // Wire service configuration for log_processors
        (new Config($config['log_processors']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }

    /**
     * {@inheritDoc}
     *
     * @return ProcessorPluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this($container, $requestedName ?: ProcessorPluginManager::class, $this->creationOptions);
    }

    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
