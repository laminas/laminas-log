<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Formatter;

use Laminas\Log\Formatter\FirePhp;
use PHPUnit\Framework\TestCase;

class FirePhpTest extends TestCase
{
    public function testFormatWithExtraData()
    {
        $fields = [ 'message' => 'foo',
                'extra' => new \stdClass() ];

        $f = new FirePhp();
        list($line, $label) = $f->format($fields);

        $this->assertContains($fields['message'], $label);
        $this->assertEquals($fields['extra'], $line);
    }

    public function testFormatWithoutExtra()
    {
        $fields = [ 'message' => 'foo' ];

        $f = new FirePhp();
        list($line, $label) = $f->format($fields);

        $this->assertContains($fields['message'], $line);
        $this->assertNull($label);
    }

    public function testFormatWithEmptyExtra()
    {
        $fields = [ 'message' => 'foo',
                'extra' => [] ];

        $f = new FirePhp();
        list($line, $label) = $f->format($fields);

        $this->assertContains($fields['message'], $line);
        $this->assertNull($label);
    }

    public function testSetDateTimeFormatDoesNothing()
    {
        $formatter = new FirePhp();

        $this->assertEquals('', $formatter->getDateTimeFormat());
        $this->assertSame($formatter, $formatter->setDateTimeFormat('r'));
        $this->assertEquals('', $formatter->getDateTimeFormat());
    }
}
