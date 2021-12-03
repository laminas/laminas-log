<?php

namespace LaminasTest\Log\Writer;

use Laminas\Log\Logger;
use Laminas\Log\Writer\ChromePhp;
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
        $this->assertInstanceOf('Laminas\Log\Writer\ChromePhp\ChromePhpInterface', $writer->getChromePhp());
    }

    public function testSetChromePhp(): void
    {
        $writer   = new ChromePhp($this->chromephp);
        $chromephp2 = new MockChromePhp();

        $writer->setChromePhp($chromephp2);
        $this->assertInstanceOf('Laminas\Log\Writer\ChromePhp\ChromePhpInterface', $writer->getChromePhp());
        $this->assertEquals($chromephp2, $writer->getChromePhp());
    }

    public function testWrite(): void
    {
        $writer = new ChromePhp($this->chromephp);
        $writer->write([
            'message' => 'my msg',
            'priority' => Logger::DEBUG
        ]);
        $this->assertEquals('my msg', $this->chromephp->calls['trace'][0]);
    }

    public function testWriteDisabled(): void
    {
        $chromephp = new MockChromePhp(false);
        $writer = new ChromePhp($chromephp);
        $writer->write([
            'message' => 'my msg',
            'priority' => Logger::DEBUG
        ]);
        $this->assertEmpty($this->chromephp->calls);
    }

    public function testConstructWithOptions(): void
    {
        $formatter = new \Laminas\Log\Formatter\Simple();
        $filter    = new \Laminas\Log\Filter\Mock();
        $writer = new ChromePhp([
            'filters'   => $filter,
            'formatter' => $formatter,
            'instance'  => $this->chromephp,
        ]);
        $this->assertInstanceOf('Laminas\Log\Writer\ChromePhp\ChromePhpInterface', $writer->getChromePhp());
        $formatter = \Closure::bind(function () {
            return $this->getFormatter();
        }, $writer, ChromePhp::class)();
        $this->assertInstanceOf(\Laminas\Log\Formatter\ChromePhp::class, $formatter);

        $filters = \Closure::bind(function () {
            return $this->filters;
        }, $writer, ChromePhp::class)();

        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }
}
