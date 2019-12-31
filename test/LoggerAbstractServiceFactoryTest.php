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
use Laminas\Log\Writer\Mongo as MongoWriter;
use Laminas\Log\Writer\MongoDB as MongoDBWriter;
use Laminas\Log\Writer\Noop;
use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;

class LoggerAbstractServiceFactoryTest extends TestCase
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
        $this->expectException(ServiceNotFoundException::class);
        $this->serviceManager->get($service);
    }

    /**
     * @group 5254
     */
    public function testRetrievesDatabaseServiceFromServiceManagerWhenEncounteringDbWriter()
    {
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

    public function testRetrievesMongoServiceFromServiceManagerWhenEncounteringMongoWriter()
    {
        if (! extension_loaded('mongo')) {
            $this->markTestSkipped('The mongo PHP extension is not available');
        }

        if (version_compare(phpversion(), '7.0', '>=')) {
            $this->markTestIncomplete('Code to test is not compatible with PHP 7 ');
        }

        $mongoClient = $this->getMockBuilder('MongoClient')
            ->disableOriginalConstructor()
            ->getMock();

        $config = new Config([
            'abstract_factories' => [LoggerAbstractServiceFactory::class],
            'services' => [
                'mongo_client' => $mongoClient,
                'config' => [
                    'log' => [
                        'Application\Log' => [
                            'writers' => [
                                [
                                    'name'     => 'mongo',
                                    'priority' => 1,
                                    'options'  => [
                                        'database'     => 'applicationdb',
                                        'collection'   => 'applicationlog',
                                        'mongo'        => 'mongo_client',
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
            if ($writer instanceof MongoWriter) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Did not find expected mongo writer');
    }

    public function testRetrievesMongoDBServiceFromServiceManagerWhenEncounteringMongoDbWriter()
    {
        if (! extension_loaded('mongodb')) {
            $this->markTestSkipped('The mongodb PHP extension is not available');
        }

        $manager = new Manager('mongodb://localhost:27017');

        $config = new Config([
            'abstract_factories' => [LoggerAbstractServiceFactory::class],
            'services' => [
                'mongo_manager' => $manager,
                'config' => [
                    'log' => [
                        'Application\Log' => [
                            'writers' => [
                                [
                                    'name'     => 'mongodb',
                                    'priority' => 1,
                                    'options'  => [
                                        'database'     => 'applicationdb',
                                        'collection'   => 'applicationlog',
                                        'manager'      => 'mongo_manager',
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
            if ($writer instanceof MongoDBWriter) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Did not find expected mongo db writer');
    }

    /**
     * @group 4455
     */
    public function testWillInjectWriterPluginManagerIfAvailable()
    {
        $writers = new WriterPluginManager(new ServiceManager());
        $mockWriter = $this->createMock('Laminas\Log\Writer\WriterInterface');
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
        $mockProcessor = $this->createMock('Laminas\Log\Processor\ProcessorInterface');
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
