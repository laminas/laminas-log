<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Laminas\Log\PsrLoggerAbstractAdapterFactory;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class PsrLoggerAbstractAdapterFactoryTest extends TestCase
{
    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * Set up LoggerAbstractServiceFactory and loggers configuration.
     */
    protected function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $config = new Config([
            'abstract_factories' => [PsrLoggerAbstractAdapterFactory::class],
            'services'           => [
                'config' => [
                    'psr_log' => [
                        'Application\Frontend' => [],
                        'Application\Backend'  => [],
                    ],
                ],
            ],
        ]);
        $config->configureServiceManager($this->serviceManager);
    }

    /**
     * @return array
     */
    public function providerValidLoggerService()
    {
        return [
            ['Application\Frontend'],
            ['Application\Backend'],
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidLoggerService()
    {
        return [
            ['Logger\Application\Unknown'],
            ['Logger\Application\Frontend'],
            ['Application\Backend\Logger'],
        ];
    }

    /**
     * @param string $service
     * @dataProvider providerValidLoggerService
     */
    public function testValidLoggerService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('Laminas\Log\PsrLoggerAdapter', $actual);
    }

    /**
     * @dataProvider providerInvalidLoggerService
     *
     * @param string $service
     */
    public function testInvalidLoggerService($service)
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->serviceManager->get($service);
    }
}
