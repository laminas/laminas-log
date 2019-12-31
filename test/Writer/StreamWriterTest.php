<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\Writer\Stream as StreamWriter;

/**
 * @group      Laminas_Log
 */
class StreamWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorThrowsWhenResourceIsNotStream()
    {
        $resource = xml_parser_create();
        try {
            new StreamWriter($resource);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf('Laminas\Log\Exception\InvalidArgumentException', $e);
            $this->assertRegExp('/not a stream/i', $e->getMessage());
        }
        xml_parser_free($resource);
    }

    public function testConstructorWithValidStream()
    {
        $stream = fopen('php://memory', 'w+');
        new StreamWriter($stream);
    }

    public function testConstructorWithValidUrl()
    {
        new StreamWriter('php://memory');
    }

    public function testConstructorThrowsWhenModeSpecifiedForExistingStream()
    {
        $stream = fopen('php://memory', 'w+');
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'existing stream');
        new StreamWriter($stream, 'w+');
    }

    public function testConstructorThrowsWhenStreamCannotBeOpened()
    {
        $this->setExpectedException('Laminas\Log\Exception\RuntimeException', 'cannot be opened');
        new StreamWriter('');
    }

    public function testWrite()
    {
        $stream = fopen('php://memory', 'w+');
        $fields = ['message' => 'message-to-log'];

        $writer = new StreamWriter($stream);
        $writer->write($fields);

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertContains($fields['message'], $contents);
    }

    public function testWriteThrowsWhenStreamWriteFails()
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new StreamWriter($stream);
        fclose($stream);

        $this->setExpectedException('Laminas\Log\Exception\RuntimeException', 'Unable to write');
        $writer->write(['message' => 'foo']);
    }

    public function testShutdownClosesStreamResource()
    {
        $writer = new StreamWriter('php://memory', 'w+');
        $writer->write(['message' => 'this write should succeed']);

        $writer->shutdown();

        $this->setExpectedException('Laminas\Log\Exception\RuntimeException', 'Unable to write');
        $writer->write(['message' => 'this write should fail']);
    }

    public function testSettingNewFormatter()
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new StreamWriter($stream);
        $expected = 'foo';

        $formatter = new SimpleFormatter($expected);
        $writer->setFormatter($formatter);

        $writer->write(['bar'=>'baz']);
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertContains($expected, $contents);
    }

    public function testAllowSpecifyingLogSeparator()
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

        $this->assertRegexp('/message1.*?::.*?message2/', $contents);
        $this->assertNotContains(PHP_EOL, $contents);
    }

    public function testAllowsSpecifyingLogSeparatorAsConstructorArgument()
    {
        $writer = new StreamWriter('php://memory', 'w+', '::');
        $this->assertEquals('::', $writer->getLogSeparator());
    }

    public function testAllowsSpecifyingLogSeparatorWithinArrayPassedToConstructor()
    {
        $options = [
            'stream'        => 'php://memory',
            'mode'          => 'w+',
            'log_separator' => '::',
        ];
        $writer = new StreamWriter($options);
        $this->assertEquals('::', $writer->getLogSeparator());
    }

    public function testConstructWithOptions()
    {
        $formatter = new \Laminas\Log\Formatter\Simple();
        $filter    = new \Laminas\Log\Filter\Mock();
        $writer = new StreamWriter([
                'filters'   => $filter,
                'formatter' => $formatter,
                'stream'        => 'php://memory',
                'mode'          => 'w+',
                'log_separator' => '::',

        ]);
        $this->assertEquals('::', $writer->getLogSeparator());
        $this->assertAttributeEquals($formatter, 'formatter', $writer);

        $filters = self::readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    public function testDefaultFormatter()
    {
        $writer = new StreamWriter('php://memory');
        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\Simple', 'formatter', $writer);
    }
}
