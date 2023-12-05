<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Exception;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Exception\RuntimeException;
use Laminas\Log\Filter\Mock as MockFilter;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\Writer\Stream as StreamWriter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function sprintf;
use function stream_get_contents;
use function xml_parser_create;
use function xml_parser_free;

use const PHP_EOL;

class StreamWriterTest extends TestCase
{
    /**
     * Flag used to prevent running tests that require full isolation
     */
    private static $ranSuite = false;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('laminas-log');
    }

    protected function tearDown(): void
    {
        self::$ranSuite = true;
    }

    public function testConstructorThrowsWhenResourceIsNotStream(): void
    {
        $resource = xml_parser_create();
        try {
            new StreamWriter($resource);
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
            $this->assertMatchesRegularExpression('/not a stream/i', $e->getMessage());
        }
        xml_parser_free($resource);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConstructorWithValidStream(): void
    {
        $stream = fopen('php://memory', 'w+');
        new StreamWriter($stream);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConstructorWithValidUrl(): void
    {
        new StreamWriter('php://memory');
    }

    public function testConstructorThrowsWhenModeSpecifiedForExistingStream(): void
    {
        $stream = fopen('php://memory', 'w+');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('existing stream');
        new StreamWriter($stream, 'w+');
    }

    /**
     * @requires PHP < 8.0
     */
    public function testConstructorThrowsWhenStreamCannotBeOpened(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot be opened');
        new StreamWriter('');
    }

    public function testWrite(): void
    {
        $stream = fopen('php://memory', 'w+');
        $fields = ['message' => 'message-to-log'];

        $writer = new StreamWriter($stream);
        $writer->write($fields);

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString($fields['message'], $contents);
    }

    /**
     * @requires PHP < 8.0
     */
    public function testWriteThrowsWhenStreamWriteFails(): void
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new StreamWriter($stream);
        fclose($stream);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to write');
        $writer->write(['message' => 'foo']);
    }

    /**
     * @requires PHP < 8.0
     */
    public function testShutdownClosesStreamResource(): void
    {
        $writer = new StreamWriter('php://memory', 'w+');
        $writer->write(['message' => 'this write should succeed']);

        $writer->shutdown();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to write');
        $writer->write(['message' => 'this write should fail']);
    }

    public function testSettingNewFormatter(): void
    {
        $stream   = fopen('php://memory', 'w+');
        $writer   = new StreamWriter($stream);
        $expected = 'foo';

        $formatter = new SimpleFormatter($expected);
        $writer->setFormatter($formatter);

        $writer->write(['bar' => 'baz']);
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString($expected, $contents);
    }

    public function testAllowSpecifyingLogSeparator(): void
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new StreamWriter($stream);
        $writer->setLogSeparator('::');

        $fields = ['message' => 'message1'];
        $writer->write($fields);
        $fields['message'] = 'message2';
        $writer->write($fields);

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertMatchesRegularExpression('/message1.*?::.*?message2/', $contents);
        $this->assertStringNotContainsString(PHP_EOL, $contents);
    }

    public function testAllowsSpecifyingLogSeparatorAsConstructorArgument(): void
    {
        $writer = new StreamWriter('php://memory', 'w+', '::');
        $this->assertEquals('::', $writer->getLogSeparator());
    }

    public function testAllowsSpecifyingLogSeparatorWithinArrayPassedToConstructor(): void
    {
        $options = [
            'stream'        => 'php://memory',
            'mode'          => 'w+',
            'log_separator' => '::',
        ];
        $writer  = new StreamWriter($options);
        $this->assertEquals('::', $writer->getLogSeparator());
    }

    public function testConstructWithOptions(): void
    {
        $formatter = new SimpleFormatter();
        $filter    = new MockFilter();

        $writer = new class ([
            'filters'       => $filter,
            'formatter'     => $formatter,
            'stream'        => 'php://memory',
            'mode'          => 'w+',
            'log_separator' => '::',
        ]) extends StreamWriter {
            public function getFormatter(): SimpleFormatter
            {
                return $this->formatter;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };

        $this->assertEquals('::', $writer->getLogSeparator());
        $this->assertEquals($formatter, $writer->getFormatter());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    public function testDefaultFormatter(): void
    {
        $writer = new class ('php://memory') extends StreamWriter {
            public function getFormatter(): SimpleFormatter
            {
                return $this->formatter;
            }
        };

        $this->assertInstanceOf(SimpleFormatter::class, $writer->getFormatter());
    }

    public function testCanSpecifyFilePermsViaChmodOption(): void
    {
        $filter    = new MockFilter();
        $formatter = new SimpleFormatter();
        $file      = $this->root->url() . '/foo';
        $writer    = new StreamWriter([
            'filters'       => $filter,
            'formatter'     => $formatter,
            'stream'        => $file,
            'mode'          => 'w+',
            'chmod'         => 0664,
            'log_separator' => '::',
        ]);

        $this->assertEquals(0664, $this->root->getChild('foo')->getPermissions());
    }

    public function testFilePermsDoNotThrowErrors(): void
    {
        // Make the chmod() override emit a warning.
        $GLOBALS['chmod_throw_error'] = true;

        $filter    = new MockFilter();
        $formatter = new SimpleFormatter();
        $file      = $this->root->url() . '/foo';
        $writer    = new StreamWriter([
            'filters'   => $filter,
            'formatter' => $formatter,
            'stream'    => $file,
            'mode'      => 'w+',
            'chmod'     => 0777,
        ]);

        $this->assertEquals(0777, $this->root->getChild('foo')->getPermissions());
    }

    public function testCanSpecifyFilePermsViaConstructorArgument(): void
    {
        if (self::$ranSuite) {
            $this->markTestSkipped(sprintf(
                'The test %s only passes when run by itself; use the --filter argument to run it in isolation',
                __FUNCTION__
            ));
        }
        $file = $this->root->url() . '/foo';
        new StreamWriter($file, null, null, 0755);
        $this->assertEquals(0755, $this->root->getChild('foo')->getPermissions());
    }
}
