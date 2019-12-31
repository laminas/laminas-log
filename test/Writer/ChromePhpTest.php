<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Writer;

use Laminas\Log\Logger;
use Laminas\Log\Writer\ChromePhp;
use LaminasTest\Log\TestAsset\MockChromePhp;

/**
 * @copyright  Copyright (c) 2005-2015 Laminas (https://www.zend.com)
 * @license    https://getlaminas.org/license/new-bsd     New BSD License
 * @group      Laminas_Log
 */
class ChromePhpTest extends \PHPUnit_Framework_TestCase
{
    protected $chromephp;

    public function setUp()
    {
        $this->chromephp = new MockChromePhp();
    }

    public function testGetChromePhp()
    {
        $writer = new ChromePhp($this->chromephp);
        $this->assertInstanceOf('Laminas\Log\Writer\ChromePhp\ChromePhpInterface', $writer->getChromePhp());
    }

    public function testSetChromePhp()
    {
        $writer   = new ChromePhp($this->chromephp);
        $chromephp2 = new MockChromePhp();

        $writer->setChromePhp($chromephp2);
        $this->assertInstanceOf('Laminas\Log\Writer\ChromePhp\ChromePhpInterface', $writer->getChromePhp());
        $this->assertEquals($chromephp2, $writer->getChromePhp());
    }

    public function testWrite()
    {
        $writer = new ChromePhp($this->chromephp);
        $writer->write(array(
            'message' => 'my msg',
            'priority' => Logger::DEBUG
        ));
        $this->assertEquals('my msg', $this->chromephp->calls['trace'][0]);
    }

    public function testWriteDisabled()
    {
        $chromephp = new MockChromePhp(false);
        $writer = new ChromePhp($chromephp);
        $writer->write(array(
            'message' => 'my msg',
            'priority' => Logger::DEBUG
        ));
        $this->assertEmpty($this->chromephp->calls);
    }

    public function testConstructWithOptions()
    {
        $formatter = new \Laminas\Log\Formatter\Simple();
        $filter    = new \Laminas\Log\Filter\Mock();
        $writer = new ChromePhp(array(
            'filters'   => $filter,
            'formatter' => $formatter,
            'instance'  => $this->chromephp,
        ));
        $this->assertInstanceOf('Laminas\Log\Writer\ChromePhp\ChromePhpInterface', $writer->getChromePhp());
        $this->assertAttributeInstanceOf('Laminas\Log\Formatter\ChromePhp', 'formatter', $writer);

        $filters = self::readAttribute($writer, 'filters');
        $this->assertCount(1, $filters);
        $this->assertEquals($filter, $filters[0]);
    }
}
