<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Logger;
use Laminas\Log\Writer\Mail as MailWriter;
use Laminas\Mail\Message as MailMessage;
use Laminas\Mail\Transport;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class MailTest extends \PHPUnit_Framework_TestCase
{
    const FILENAME = 'message.txt';
    /**
     * @var MailWriter
     */
    protected $writer;
    /**
     * @var Logger
     */
    protected $log;

    protected function setUp()
    {
        $message = new MailMessage();
        $transport = new Transport\File();
        $options   = new Transport\FileOptions(array(
            'path'      => __DIR__,
            'callback'  => function (Transport\File $transport) {
                return MailTest::FILENAME;
            },
        ));
        $transport->setOptions($options);

        $this->writer = new MailWriter($message, $transport);
        $this->log = new Logger();
        $this->log->addWriter($this->writer);
    }

    protected function tearDown()
    {
        @unlink(__DIR__. '/' . self::FILENAME);
    }

    /**
     * Tests normal logging, but with multiple messages for a level.
     *
     * @return void
     */
    public function testNormalLoggingMultiplePerLevel()
    {
        $this->log->info('an info message');
        $this->log->info('a second info message');
        unset($this->log);

        $contents = file_get_contents(__DIR__ . '/' . self::FILENAME);
        $this->assertContains('an info message', $contents);
        $this->assertContains('a second info message', $contents);
    }

    public function testSetSubjectPrependText()
    {
        $this->writer->setSubjectPrependText('test');

        $this->log->info('an info message');
        $this->log->info('a second info message');
        unset($this->log);

        $contents = file_get_contents(__DIR__ . '/' . self::FILENAME);
        $this->assertContains('an info message', $contents);
        $this->assertContains('Subject: test', $contents);
    }

    public function testConstructWithOptions()
    {
        $message   = new MailMessage();
        $transport = new Transport\File();
        $options   = new Transport\FileOptions(array(
                'path'      => __DIR__,
                'callback'  => function (Transport\File $transport) {
                    return MailTest::FILENAME;
                },
        ));
        $transport->setOptions($options);

        $formatter = new \Laminas\Log\Formatter\Simple();
        $filter    = new \Laminas\Log\Filter\Mock();
        $writer = new MailWriter(array(
                'filters'   => $filter,
                'formatter' => $formatter,
                'mail'      => $message,
                'transport' => $transport,
        ));

        $this->assertAttributeEquals($message, 'mail', $writer);
        $this->assertAttributeEquals($transport, 'transport', $writer);
        $this->assertAttributeEquals($formatter, 'formatter', $writer);

        $filters = self::readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }
}
