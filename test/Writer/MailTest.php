<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Laminas\Log\Filter\Mock;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Mail as MailWriter;
use Laminas\Mail\Message as MailMessage;
use Laminas\Mail\Transport;
use Laminas\Mail\Transport\Smtp;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_get_contents;
use function unlink;

class MailTest extends TestCase
{
    public const FILENAME = 'message.txt';

    /** @var MailWriter */
    protected $writer;

    /** @var Logger */
    protected $log;

    protected function setUp(): void
    {
        $message   = new MailMessage();
        $transport = new Transport\File();
        $options   = new Transport\FileOptions([
            'path'     => __DIR__,
            'callback' => static fn(Transport\File $transport) => self::FILENAME,
        ]);
        $transport->setOptions($options);

        $this->writer = new MailWriter($message, $transport);
        $this->log    = new Logger();
        $this->log->addWriter($this->writer);
    }

    protected function tearDown(): void
    {
        if (file_exists(__DIR__ . '/' . self::FILENAME)) {
            unlink(__DIR__ . '/' . self::FILENAME);
        }
    }

    /**
     * Tests normal logging, but with multiple messages for a level.
     */
    public function testNormalLoggingMultiplePerLevel(): void
    {
        $this->log->info('an info message');
        $this->log->info('a second info message');
        unset($this->log);

        $contents = file_get_contents(__DIR__ . '/' . self::FILENAME);
        $this->assertStringContainsString('an info message', $contents);
        $this->assertStringContainsString('a second info message', $contents);
    }

    public function testSetSubjectPrependText(): void
    {
        $this->writer->setSubjectPrependText('test');

        $this->log->info('an info message');
        $this->log->info('a second info message');
        unset($this->log);

        $contents = file_get_contents(__DIR__ . '/' . self::FILENAME);
        $this->assertStringContainsString('an info message', $contents);
        $this->assertStringContainsString('Subject: test', $contents);
    }

    public function testConstructWithOptions(): void
    {
        $message   = new MailMessage();
        $transport = new Transport\File();
        $options   = new Transport\FileOptions([
            'path'     => __DIR__,
            'callback' => static fn(Transport\File $transport) => self::FILENAME,
        ]);
        $transport->setOptions($options);

        $formatter = new Simple();
        $filter    = new Mock();
        $writer    = new class ([
            'filters'   => $filter,
            'formatter' => $formatter,
            'mail'      => $message,
            'transport' => $transport,
        ]) extends MailWriter {
            public function getMail(): MailMessage
            {
                return $this->mail;
            }

            public function getTransport(): Transport\TransportInterface
            {
                return $this->transport;
            }

            public function getFormatter()
            {
                return $this->formatter;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };

        $this->assertEquals($message, $writer->getMail());
        $this->assertEquals($transport, $writer->getTransport());
        $this->assertEquals($formatter, $writer->getFormatter());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    public function testConstructWithMailAsArrayOptions(): void
    {
        $messageOptions = [
            'encoding' => 'UTF-8',
            'from'     => 'matthew@example.com',
            'to'       => 'api-tools-devteam@example.com',
            'subject'  => 'subject',
            'body'     => 'body',
        ];

        $writer = new class ([
            'mail' => $messageOptions,
        ]) extends MailWriter {
            public function getMail(): MailMessage
            {
                return $this->mail;
            }
        };

        $this->assertInstanceOf(MailMessage::class, $writer->getMail());
    }

    public function testConstructWithMailTransportAsArrayOptions(): void
    {
        $messageOptions = [
            'encoding' => 'UTF-8',
            'from'     => 'matthew@example.com',
            'to'       => 'api-tools-devteam@example.com',
            'subject'  => 'subject',
            'body'     => 'body',
        ];

        $transportOptions = [
            'type'    => 'smtp',
            'options' => [
                'host'              => 'test.dev',
                'connection_class'  => 'login',
                'connection_config' => [
                    'username'      => 'foo',
                    'smtp_password' => 'bar',
                    'ssl'           => 'tls',
                ],
            ],
        ];

        $writer = new class ([
            'mail'      => $messageOptions,
            'transport' => $transportOptions,
        ]) extends MailWriter {
            public function getTransport(): Transport\TransportInterface
            {
                return $this->transport;
            }
        };

        $this->assertInstanceOf(Smtp::class, $writer->getTransport());
    }
}
