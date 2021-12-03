<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer;

use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Filter\Mock;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\Writer\FirePhp;
use Laminas\Log\Writer\FirePhp\FirePhpInterface;
use LaminasTest\Log\TestAsset\MockFirePhp;
use PHPUnit\Framework\TestCase;
use stdClass;

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
        $this->assertInstanceOf(FirePhpInterface::class, $writer->getFirePhp());
    }

    /**
     * Test set firephp
     */
    public function testSetFirePhp(): void
    {
        $writer   = new FirePhp($this->firephp);
        $firephp2 = new MockFirePhp();

        $writer->setFirePhp($firephp2);
        $this->assertInstanceOf(FirePhpInterface::class, $writer->getFirePhp());
        $this->assertEquals($firephp2, $writer->getFirePhp());
    }

    /**
     * Test write
     */
    public function testWrite(): void
    {
        $writer = new FirePhp($this->firephp);
        $writer->write([
            'message'  => 'my msg',
            'priority' => Logger::DEBUG,
        ]);
        $this->assertEquals('my msg', $this->firephp->calls['trace'][0]);
    }

    /**
     * Test write with FirePhp disabled
     */
    public function testWriteDisabled(): void
    {
        $firephp = new MockFirePhp(false);
        $writer  = new FirePhp($firephp);
        $writer->write([
            'message'  => 'my msg',
            'priority' => Logger::DEBUG,
        ]);
        $this->assertEmpty($this->firephp->calls);
    }

    public function testConstructWithOptions(): void
    {
        $formatter = new Simple();
        $filter    = new Mock();
        $writer    = new class ([
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
        $this->assertInstanceOf(FirePhpInterface::class, $writer->getFirePhp());
        $this->assertInstanceOf(\Laminas\Log\Formatter\FirePhp::class, $writer->getFormatter());

        $filters = $writer->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }

    /**
     * Verify behavior of __construct when 'instance' is not an FirePhpInterface
     */
    public function testConstructWithInvalidInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a valid FirePhp\FirePhpInterface');
        new FirePhp(new stdClass());
    }
}
