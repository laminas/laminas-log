<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log;

class Module
{
    /**
     * Return default laminas-log configuration for laminas-mvc applications.
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register specifications for all laminas-log plugin managers with the ServiceListener.
     *
     * @param \Laminas\ModuleManager\ModuleManager $moduleManager
     * @return void
     */
    public function init($moduleManager)
    {
        $event = $moduleManager->getEvent();
        $container = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'LogProcessorManager',
            'log_processors',
            'Laminas\ModuleManager\Feature\LogProcessorProviderInterface',
            'getLogProcessorConfig'
        );

        $serviceListener->addServiceManager(
            'LogWriterManager',
            'log_writers',
            'Laminas\ModuleManager\Feature\LogWriterProviderInterface',
            'getLogWriterConfig'
        );

        $serviceListener->addServiceManager(
            'LogFilterManager',
            'log_filters',
            'Laminas\Log\Filter\LogFilterProviderInterface',
            'getLogFilterConfig'
        );

        $serviceListener->addServiceManager(
            'LogFormatterManager',
            'log_formatters',
            'Laminas\Log\Formatter\LogFormatterProviderInterface',
            'getLogFormatterConfig'
        );
    }
}
