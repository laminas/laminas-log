<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Closure;
use Laminas\Log\Filter\Mock;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\Writer\ChromePhp;
use Laminas\Log\Writer\ChromePhp\ChromePhpInterface;
use LaminasTest\Log\TestAsset\MockChromePhp;
use PHPUnit\Framework\TestCase;

class ChromePhpTest extends TestCase
{
    protected $chromephp;

    protected function setUp(): void
    {
        $this->chromephp = new MockChromePhp();
    }

    public function testGetChromePhp(): void
    {
        $writer = new ChromePhp($this->chromephp);
        $this->assertInstanceOf(ChromePhpInterface::class, $writer->getChromePhp());
    }

    public function testSetChromePhp(): void
    {
        $writer     = new ChromePhp($this->chromephp);
        $chromephp2 = new MockChromePhp();

        $writer->setChromePhp($chromephp2);
        $this->assertInstanceOf(ChromePhpInterface::class, $writer->getChromePhp());
        $this->assertEquals($chromephp2, $writer->getChromePhp());
    }

    public function testWrite(): void
    {
        $writer = new ChromePhp($this->chromephp);
        $writer->write([
            'message'  => 'my msg',
            'priority' => Logger::DEBUG,
        ]);
        $this->assertEquals('my msg', $this->chromephp->calls['trace'][0]);
    }

    public function testWriteDisabled(): void
    {
        $chromephp = new MockChromePhp(false);
        $writer    = new ChromePhp($chromephp);
        $writer->write([
            'message'  => 'my msg',
            'priority' => Logger::DEBUG,
        ]);
        $this->assertEmpty($this->chromephp->calls);
    }

    public function testConstructWithOptions(): void
    {
        $formatter = new Simple();
        $filter    = new Mock();
        $writer    = new ChromePhp([
            'filters'   => $filter,
            'formatter' => $formatter,
            'instance'  => $this->chromephp,
        ]);
        $this->assertInstanceOf(ChromePhpInterface::class, $writer->getChromePhp());
        $formatter = Closure::bind(fn() => $this->getFormatter(), $writer, ChromePhp::class)();
        $this->assertInstanceOf(\Laminas\Log\Formatter\ChromePhp::class, $formatter);

        $filters = Closure::bind(fn() => $this->filters, $writer, ChromePhp::class)();

        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }
}
