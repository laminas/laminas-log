<?php

declare(strict_types=1);

namespace LaminasTest\Log\Formatter;

use Laminas\Log\Formatter\FirePhp;
use PHPUnit\Framework\TestCase;
use stdClass;

class FirePhpTest extends TestCase
{
    public function testFormatWithExtraData(): void
    {
        $fields = [
            'message' => 'foo',
            'extra'   => new stdClass(),
        ];

        $f              = new FirePhp();
        [$line, $label] = $f->format($fields);

        $this->assertStringContainsString($fields['message'], $label);
        $this->assertEquals($fields['extra'], $line);
    }

    public function testFormatWithoutExtra(): void
    {
        $fields = ['message' => 'foo'];

        $f              = new FirePhp();
        [$line, $label] = $f->format($fields);

        $this->assertStringContainsString($fields['message'], $line);
        $this->assertNull($label);
    }

    public function testFormatWithEmptyExtra(): void
    {
        $fields = [
            'message' => 'foo',
            'extra'   => [],
        ];

        $f              = new FirePhp();
        [$line, $label] = $f->format($fields);

        $this->assertStringContainsString($fields['message'], $line);
        $this->assertNull($label);
    }

    public function testSetDateTimeFormatDoesNothing(): void
    {
        $formatter = new FirePhp();

        $this->assertEquals('', $formatter->getDateTimeFormat());
        $this->assertSame($formatter, $formatter->setDateTimeFormat('r'));
        $this->assertEquals('', $formatter->getDateTimeFormat());
    }
}
