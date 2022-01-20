<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Closure;
use DateTime;
use Laminas\Db\Adapter\Adapter;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Exception\RuntimeException;
use Laminas\Log\Filter\Mock;
use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Writer\Db as DbWriter;
use LaminasTest\Log\TestAsset\MockDbAdapter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function array_keys;
use function count;
use function sprintf;

class DbTest extends TestCase
{
    protected function setUp(): void
    {
        $this->tableName = 'db-table-name';

        $this->db     = new MockDbAdapter();
        $this->writer = new DbWriter($this->db, $this->tableName);
    }

    public function testNotPassingTableNameToConstructorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('table name');
        $writer = new DbWriter($this->db);
    }

    public function testNotPassingDbToConstructorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Adapter');
        $writer = new DbWriter([]);
    }

    public function testPassingTableNameAsArgIsOK(): void
    {
        $options = [
            'db'    => $this->db,
            'table' => $this->tableName,
        ];
        $writer  = new DbWriter($options);
        $this->assertInstanceOf(DbWriter::class, $writer);
        $tableName = Closure::bind(fn() => $this->tableName, $writer, DbWriter::class)();
        $this->assertSame($this->tableName, $tableName);
    }

    public function testWriteWithDefaults(): void
    {
        // log to the mock db adapter
        $fields = [
            'message'  => 'foo',
            'priority' => 42,
        ];

        $this->writer->write($fields);

        // insert should be called once...
        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));
        $this->assertContains('execute', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['execute']));
        $this->assertEquals([$fields], $this->db->calls['execute'][0]);
    }

    public function testWriteWithDefaultsUsingArray(): void
    {
        // log to the mock db adapter
        $message  = 'message-to-log';
        $priority = 2;
        $events   = [
            'file' => 'test',
            'line' => 1,
        ];
        $this->writer->write([
            'message'  => $message,
            'priority' => $priority,
            'events'   => $events,
        ]);
        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));

        $binds = [
            'message'     => $message,
            'priority'    => $priority,
            'events_line' => $events['line'],
            'events_file' => $events['file'],
        ];
        $this->assertEquals([$binds], $this->db->calls['execute'][0]);
    }

    public function testWriteWithDefaultsUsingArrayAndSeparator(): void
    {
        $this->writer = new DbWriter($this->db, $this->tableName, null, '-');

        // log to the mock db adapter
        $message  = 'message-to-log';
        $priority = 2;
        $events   = [
            'file' => 'test',
            'line' => 1,
        ];
        $this->writer->write([
            'message'  => $message,
            'priority' => $priority,
            'events'   => $events,
        ]);
        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));

        $binds = [
            'message'     => $message,
            'priority'    => $priority,
            'events-line' => $events['line'],
            'events-file' => $events['file'],
        ];
        $this->assertEquals([$binds], $this->db->calls['execute'][0]);
    }

    public function testWriteUsesOptionalCustomColumnNames(): void
    {
        $this->writer = new DbWriter($this->db, $this->tableName, [
            'message'  => 'new-message-field',
            'priority' => 'new-priority-field',
        ]);

        // log to the mock db adapter
        $message  = 'message-to-log';
        $priority = 2;
        $this->writer->write([
            'message'  => $message,
            'priority' => $priority,
        ]);

        // insert should be called once...
        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));

        // ...with the correct table and binds for the database
        $binds = [
            'new-message-field'  => $message,
            'new-priority-field' => $priority,
        ];
        $this->assertEquals([$binds], $this->db->calls['execute'][0]);
    }

    public function testWriteUsesParamsWithArray(): void
    {
        $this->writer = new DbWriter($this->db, $this->tableName, [
            'message'  => 'new-message-field',
            'priority' => 'new-priority-field',
            'events'   => [
                'line' => 'new-line',
                'file' => 'new-file',
            ],
        ]);

        // log to the mock db adapter
        $message  = 'message-to-log';
        $priority = 2;
        $events   = [
            'file' => 'test',
            'line' => 1,
        ];
        $this->writer->write([
            'message'  => $message,
            'priority' => $priority,
            'events'   => $events,
        ]);
        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));
        // ...with the correct table and binds for the database
        $binds = [
            'new-message-field'  => $message,
            'new-priority-field' => $priority,
            'new-line'           => $events['line'],
            'new-file'           => $events['file'],
        ];
        $this->assertEquals([$binds], $this->db->calls['execute'][0]);
    }

    public function testShutdownRemovesReferenceToDatabaseInstance(): void
    {
        $this->writer->write(['message' => 'this should not fail']);
        $this->writer->shutdown();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database adapter is null');
        $this->writer->write(['message' => 'this should fail']);
    }

    public function testWriteDateTimeAsTimestamp(): void
    {
        $date  = new DateTime();
        $event = ['timestamp' => $date];
        $this->writer->write($event);

        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));

        $this->assertEquals([
            [
                'timestamp' => $date->format(FormatterInterface::DEFAULT_DATETIME_FORMAT),
            ],
        ], $this->db->calls['execute'][0]);
    }

    public function testWriteDateTimeAsExtraValue(): void
    {
        $date  = new DateTime();
        $event = [
            'extra' => [
                'request_time' => $date,
            ],
        ];
        $this->writer->write($event);

        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));

        $this->assertEquals([
            [
                'extra_request_time' => $date->format(FormatterInterface::DEFAULT_DATETIME_FORMAT),
            ],
        ], $this->db->calls['execute'][0]);
    }

    public function testConstructWithOptions(): void
    {
        $formatter = new Simple();
        $filter    = new Mock();
        $writer    = new class ([
            'filters'   => $filter,
            'formatter' => $formatter,
            'table'     => $this->tableName,
            'db'        => $this->db,
        ]) extends DbWriter {
            public function getTableName(): string
            {
                return $this->tableName;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }

            public function getFormatter()
            {
                return $this->formatter;
            }

            public function getDb(): Adapter
            {
                return $this->db;
            }
        };
        $this->assertInstanceOf(DbWriter::class, $writer);
        $this->assertSame($this->tableName, $writer->getTableName());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);

        $registeredFormatter = $writer->getFormatter();
        $this->assertSame($formatter, $registeredFormatter);

        $registeredDb = $writer->getDb();
        $this->assertSame($this->db, $registeredDb);
    }

    /**
     * @group 2589
     */
    public function testMapEventIntoColumnDoesNotTriggerArrayToStringConversion(): void
    {
        $this->writer = new DbWriter($this->db, $this->tableName, [
            'priority' => 'new-priority-field',
            'message'  => 'new-message-field',
            'extra'    => [
                'file'  => 'new-file',
                'line'  => 'new-line',
                'trace' => 'new-trace',
            ],
        ]);

        // log to the mock db adapter
        $priority = 2;
        $message  = 'message-to-log';
        $extra    = [
            'file'  => 'test.php',
            'line'  => 1,
            'trace' => [
                [
                    'function' => 'Bar',
                    'class'    => 'Foo',
                    'type'     => '->',
                    'args'     => [
                        'baz',
                    ],
                ],
            ],
        ];
        $this->writer->write([
            'priority' => $priority,
            'message'  => $message,
            'extra'    => $extra,
        ]);

        $this->assertContains('query', array_keys($this->db->calls));
        $this->assertEquals(1, count($this->db->calls['query']));

        foreach ($this->db->calls['execute'][0][0] as $fieldName => $fieldValue) {
            $this->assertIsString($fieldName);
            $this->assertIsString((string) $fieldValue);
        }
    }

    /**
     * @group 2589
     */
    public function testMapEventIntoColumnMustReturnScalarValues(): void
    {
        $event = [
            'priority' => 2,
            'message'  => 'message-to-log',
            'extra'    => [
                'file'  => 'test.php',
                'line'  => 1,
                'trace' => [
                    [
                        'function' => 'Bar',
                        'class'    => 'Foo',
                        'type'     => '->',
                        'args'     => [
                            'baz',
                        ],
                    ],
                ],
            ],
        ];

        $columnMap = [
            'priority' => 'new-priority-field',
            'message'  => 'new-message-field',
            'extra'    => [
                'file'  => 'new-file',
                'line'  => 'new-line',
                'trace' => 'new-trace',
            ],
        ];

        $method = new ReflectionMethod($this->writer, 'mapEventIntoColumn');
        $method->setAccessible(true);
        $data = $method->invoke($this->writer, $event, $columnMap);

        foreach ($data as $field => $value) {
            $this->assertIsScalar($value, sprintf(
                'Value of column "%s" should be scalar',
                $field
            ));
        }
    }

    public function testEventIntoColumnMustReturnScalarValues(): void
    {
        $event = [
            'priority' => 2,
            'message'  => 'message-to-log',
            'extra'    => [
                'file'  => 'test.php',
                'line'  => 1,
                'trace' => [
                    [
                        'function' => 'Bar',
                        'class'    => 'Foo',
                        'type'     => '->',
                        'args'     => [
                            'baz',
                        ],
                    ],
                ],
            ],
        ];

        $method = new ReflectionMethod($this->writer, 'eventIntoColumn');
        $method->setAccessible(true);
        $data = $method->invoke($this->writer, $event);

        foreach ($data as $field => $value) {
            $this->assertIsScalar($value, sprintf(
                'Value of column "%s" should be scalar',
                $field
            ));
        }
    }
}
