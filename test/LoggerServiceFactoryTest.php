<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Closure;
use Laminas\Db\Adapter\Adapter;
use Laminas\Log\Logger;
use Laminas\Log\LoggerServiceFactory;
use Laminas\Log\Processor\ProcessorInterface;
use Laminas\Log\ProcessorPluginManager;
use Laminas\Log\Writer\Db as DbWriter;
use Laminas\Log\Writer\MongoDB as MongoDBWriter;
use Laminas\Log\Writer\Noop;
use Laminas\Log\Writer\WriterInterface;
use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayObject;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

use function count;
use function extension_loaded;

class LoggerServiceFactoryTest extends TestCase
{
    protected ServiceLocatorInterface $serviceManager;

    /**
     * Set up LoggerServiceFactory and loggers configuration.
     */
    protected function setUp(): void
    {
        $this->serviceManager = new ServiceManager();
        $config               = new Config([
            'aliases'   => [
                'Laminas\Log' => Logger::class,
            ],
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'config' => [
                    'log' => [],
                ],
            ],
        ]);
        $config->configureServiceManager($this->serviceManager);
    }

    public function providerValidLoggerService(): array
    {
        return [
            [Logger::class],
            ['Laminas\Log'],
        ];
    }

    public function providerInvalidLoggerService(): array
    {
        return [
            ['log'],
            ['Logger\Application\Frontend'],
            ['writers'],
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @dataProvider providerValidLoggerService
     */
    public function testValidLoggerService(string $service): void
    {
        $actual = $this->serviceManager->get($service);
        self::assertInstanceOf(Logger::class, $actual);
    }

    /**
     * @dataProvider providerInvalidLoggerService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvalidLoggerService(string $service): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->serviceManager->get($service);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRetrievesDatabaseServiceWhenUsingDbWriter(): void
    {
        $db = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config         = new Config([
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'Db\Logger' => $db,
                'config'    => [
                    'log' => [
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
        ]);
        $serviceManager = new ServiceManager();
        $config->configureServiceManager($serviceManager);

        $logger = $serviceManager->get(Logger::class);
        self::assertInstanceOf(Logger::class, $logger);
        $writers = $logger->getWriters();
        $found   = false;

        $writer = null;
        foreach ($writers as $writer) {
            if ($writer instanceof DbWriter) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Did not find expected DB writer');

        $writerDb = Closure::bind(function () {
            return $this->db;
        }, $writer, DbWriter::class)();

        self::assertSame($db, $writerDb);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRetrievesMongoDBServiceWhenUsingMongoDbWriter(): void
    {
        if (! extension_loaded('mongodb')) {
            self::markTestSkipped('The mongodb PHP extension is not available');
        }

        $manager = new Manager('mongodb://localhost:27017');

        $config         = new Config([
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'mongo_manager' => $manager,
                'config'        => [
                    'log' => [
                        'writers' => [
                            [
                                'name'     => 'mongodb',
                                'priority' => 1,
                                'options'  => [
                                    'database'   => 'applicationdb',
                                    'collection' => 'applicationlog',
                                    'manager'    => 'mongo_manager',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $serviceManager = new ServiceManager();
        $config->configureServiceManager($serviceManager);

        $logger = $serviceManager->get(Logger::class);
        self::assertInstanceOf(Logger::class, $logger);

        $found = false;
        foreach ($logger->getWriters() as $writer) {
            if ($writer instanceof MongoDBWriter) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Did not find expected mongo db writer');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWillInjectWriterPluginManagerIfAvailable(): void
    {
        $writers    = new WriterPluginManager(new ServiceManager());
        $mockWriter = $this->createMock(WriterInterface::class);
        $writers->setService('CustomWriter', $mockWriter);

        $config   = new Config([
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'LogWriterManager' => $writers,
                'config'           => [
                    'log' => [
                        'writers' => [['name' => 'CustomWriter']],
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);

        $log        = $services->get(Logger::class);
        $logWriters = $log->getWriters();
        self::assertEquals(1, count($logWriters));
        $writer = $logWriters->current();
        self::assertSame($mockWriter, $writer);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWillInjectProcessorPluginManagerIfAvailable(): void
    {
        $processors    = new ProcessorPluginManager(new ServiceManager());
        $mockProcessor = $this->createMock(ProcessorInterface::class);
        $processors->setService('CustomProcessor', $mockProcessor);

        $config   = new Config([
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'LogProcessorManager' => $processors,
                'config'              => [
                    'log' => [
                        'writers'    => [['name' => Noop::class]],
                        'processors' => [['name' => 'CustomProcessor']],
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);

        $log           = $services->get(Logger::class);
        $logProcessors = $log->getProcessors();
        self::assertEquals(1, count($logProcessors));
        $processor = $logProcessors->current();
        self::assertSame($mockProcessor, $processor);
    }

    /**
     * @dataProvider dataWritersValues()
     * @param int|string|object $writers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWritersValue($writers, $count): void
    {
        $config   = new Config([
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'config' => [
                    'log' => [
                        'writers' => $writers,
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);

        /** @var Logger $log */
        $log = $services->get(Logger::class);
        self::assertCount($count, $log->getWriters());
    }

    public function dataWritersValues(): array
    {
        return [
            'null'           => [null, 0],
            'string'         => ['writers config', 0],
            'number'         => [1e3, 0],
            'object'         => [new stdClass(), 0],
            'empty iterable' => [new ArrayObject(), 0],
            'iterable'       => [new ArrayObject([['name' => Noop::class]]), 1],
        ];
    }

    /**
     * @dataProvider dataInvalidWriterConfig()
     * @param string|object $value
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testInvalidWriterConfig($value, string $type): void
    {
        $config   = new Config([
            'factories' => [
                Logger::class => LoggerServiceFactory::class,
            ],
            'services'  => [
                'config' => [
                    'log' => [
                        'writers' => [
                            $value,
                        ],
                    ],
                ],
            ],
        ]);
        $services = new ServiceManager();
        $config->configureServiceManager($services);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('config log.writers[] must contain array or ArrayAccess, ' . $type . ' provided');

        $services->get(Logger::class);
    }

    public function dataInvalidWriterConfig(): array
    {
        return [
            'string' => ['invalid config', 'string'],
            'object' => [new stdClass(), 'stdClass'],
        ];
    }
}
