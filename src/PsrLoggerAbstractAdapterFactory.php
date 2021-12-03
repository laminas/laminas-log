<?php

declare(strict_types=1);

namespace Laminas\Log;

use Interop\Container\ContainerInterface;

/**
 * PSR Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class PsrLoggerAbstractAdapterFactory extends LoggerAbstractServiceFactory
{
    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey = 'psr_log';

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $logger = parent::__invoke($container, $requestedName, $options);

        return new PsrLoggerAdapter($logger);
    }
}
