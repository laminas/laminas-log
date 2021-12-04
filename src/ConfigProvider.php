<?php

declare(strict_types=1);

namespace Laminas\Log;

class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'            => [
                //phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
                \Zend\Log\Logger::class => Logger::class,
                //phpcs:enable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
            ],
            'abstract_factories' => [
                LoggerAbstractServiceFactory::class,
                PsrLoggerAbstractAdapterFactory::class,
            ],
            'factories'          => [
                Logger::class         => LoggerServiceFactory::class,
                'LogFilterManager'    => FilterPluginManagerFactory::class,
                'LogFormatterManager' => FormatterPluginManagerFactory::class,
                'LogProcessorManager' => ProcessorPluginManagerFactory::class,
                'LogWriterManager'    => WriterPluginManagerFactory::class,
            ],
        ];
    }
}
