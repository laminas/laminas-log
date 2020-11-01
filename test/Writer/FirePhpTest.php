<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Logger;
use Laminas\Log\Writer\FirePhp;
use LaminasTest\Log\TestAsset\MockFirePhp;
use PHPUnit\Framework\TestCase;

class FirePhpTest extends TestCase
{
    protected $firephp;

    protected function setUp(): void
    {
        $this->firephp = new MockFirePhp();
    }
    /**
     * Test get FirePhp
     */
    public function testGetFirePhp(): void
    {
        $writer = new FirePhp($this->firephp);
        $this->assertInstanceOf('Laminas\Log\Writer\FirePhp\FirePhpInterface', $writer->getFirePhp());
    }
    /**
     * Test set firephp
     */
    public function testSetFirePhp(): void
    {
        $writer   = new FirePhp($this->firephp);
        $firephp2 = new MockFirePhp();

        $writer->setFirePhp($firephp2);
        $this->assertInstanceOf('Laminas\Log\Writer\FirePhp\FirePhpInterface', $writer->getFirePhp());
        $this->assertEquals($firephp2, $writer->getFirePhp());
    }
    /**
     * Test write
     */
    public function testWrite(): void
    {
        $writer = new FirePhp($this->firephp);
        $writer->write([
            'message' => 'my msg',
            'priority' => Logger::DEBUG
        ]);
        $this->assertEquals('my msg', $this->firephp->calls['trace'][0]);
    }
    /**
     * Test write with FirePhp disabled
     */
    public function testWriteDisabled(): void
    {
        $firephp = new MockFirePhp(false);
        $writer = new FirePhp($firephp);
        $writer->write([
            'message' => 'my msg',
            'priority' => Logger::DEBUG
        ]);
        $this->assertEmpty($this->firephp->calls);
    }

    public function testConstructWithOptions(): void
    {
        $formatter = new \Laminas\Log\Formatter\Simple();
        $filter    = new \Laminas\Log\Filter\Mock();
        $writer = new class([
            'filters'   => $filter,
            'formatter' => $formatter,
            'instance'  => $this->firephp,
        ]) extends FirePhp {
            public function getFormatter()
            {
                return $this->formatter;
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };
        $this->assertInstanceOf('Laminas\Log\Writer\FirePhp\FirePhpInterface', $writer->getFirePhp());
        $this->assertInstanceOf('Laminas\Log\Formatter\FirePhp', $writer->getFormatter());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    /**
     * Verify behavior of __construct when 'instance' is not an FirePhpInterface
     */
    public function testConstructWithInvalidInstance(): void
    {
        $this->expectException(\Laminas\Log\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a valid FirePhp\FirePhpInterface');
        new FirePhp(new \StdClass());
    }
}
