<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Laminas\Log\LoggerAbstractServiceFactory;
use Laminas\Log\ProcessorPluginManager;
use Laminas\Log\Writer\Db as DbWriter;
use Laminas\Log\Writer\Noop;
use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;

/**
 * @group      Laminas_Log
 */
class LoggerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * Set up LoggerAbstractServiceFactory and loggers configuration.
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->serviceManager = new ServiceManager();
        $config = new Config([
            'abstract_factories' => [LoggerAbstractServiceFactory::class],
            'services' => [
                'config' => [
                    'log' => [
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
        $this->assertInstanceOf('Laminas\Log\Logger', $actual);
    }

    /**
     * @dataProvider providerInvalidLoggerService
     *
     * @param string $service
     */
    public function testInvalidLoggerService($service)
    {
        $this->setExpectedException(ServiceNotFoundException::class);
        $this->serviceManager->get($service);
    }

    /**
     * @group 5254
     */
    public function testRetrievesDatabaseServiceFromServiceManagerWhenEncounteringDbWriter()
    {
        if (! class_exists('Laminas\Db\Adapter\Adapter')) {
            $this->markTestSkipped(
                'laminas-db related tests are disabled when testing laminas-servicemanager v3 '
                . 'forwards compatibility, until laminas-db is also forwards compatible'
            );
        }

        $db = $this->getMockBuilder('Laminas\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();

        $config = new Config([
            'abstract_factories' => [LoggerAbstractServiceFactory::class],
            'services' => [
                'Db\Logger' => $db,
                'config' => [
                    'log' => [
                        'Application\Log' => [
                            'writers' => [
                                [
                                    'name'     => 'db',
                                    'priority' => 1,
                                    'options'  => [
                                        'separator' => '_',
                                        'column'    => [],
                                        'table'     => 'applicationlog',
                                        'db'        => 'Db\Logger',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $serviceManager = new ServiceManager();
        $config->configureServiceManager($serviceManager);

        $logger = $serviceManager->get('Application\Log');
        $this->assertInstanceOf('Laminas\Log\Logger', $logger);
        $writers = $logger->getWriters();
        $found   = false;

        foreach ($writers as $writer) {
            if ($writer instanceof DbWriter) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Did not find expected DB writer');
        $this->assertAttributeSame($db, 'db', $writer);
    }

    /**
     * @group 4455
     */
    public function testWillInjectWriterPluginManagerIfAvailable()
    {
        $writers = new WriterPluginManager(new ServiceManager());
        $mockWriter = $this->getMock('Laminas\Log\Writer\WriterInterface');
        $writers->setService('CustomWriter', $mockWriter);

        $config = new Config([
            'abstract_factories' => [LoggerAbstractServiceFactory::class],
            'services' => [
                'LogWriterManager' => $writers,
                'config' => [
                    'log' => [
                        'Application\Frontend' => [
                            'writers' => [['name' => 'CustomWriter']],
                        ],
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);

        $log = $services->get('Application\Frontend');
        $logWriters = $log->getWriters();
        $this->assertEquals(1, count($logWriters));
        $writer = $logWriters->current();
        $this->assertSame($mockWriter, $writer);
    }

    /**
     * @group 4455
     */
    public function testWillInjectProcessorPluginManagerIfAvailable()
    {
        $processors = new ProcessorPluginManager(new ServiceManager());
        $mockProcessor = $this->getMock('Laminas\Log\Processor\ProcessorInterface');
        $processors->setService('CustomProcessor', $mockProcessor);

        $config = new Config([
            'abstract_factories' => [LoggerAbstractServiceFactory::class],
            'services' => [
                'LogProcessorManager' => $processors,
                'config' => [
                    'log' => [
                        'Application\Frontend' => [
                            'writers'    => [['name' => Noop::class]],
                            'processors' => [['name' => 'CustomProcessor']],
                        ],
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);

        $log = $services->get('Application\Frontend');
        $logProcessors = $log->getProcessors();
        $this->assertEquals(1, count($logProcessors));
        $processor = $logProcessors->current();
        $this->assertSame($mockProcessor, $processor);
    }
}
