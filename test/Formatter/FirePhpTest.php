<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Formatter;

use Laminas\Log\Formatter\FirePhp;

/**
 * @copyright  Copyright (c) 2005-2013 Laminas (https://www.zend.com)
 * @license    https://getlaminas.org/license/new-bsd     New BSD License
 * @group      Laminas_Log
 */
class FirePhpTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatWithExtraData()
    {
        $fields = array( 'message' => 'foo',
                'extra' => new \stdClass() );

        $f = new FirePhp();
        list($line, $label) = $f->format($fields);

        $this->assertContains($fields['message'], $label);
        $this->assertEquals($fields['extra'], $line);
    }

    public function testFormatWithoutExtra()
    {
        $fields = array( 'message' => 'foo' );

        $f = new FirePhp();
        list($line, $label) = $f->format($fields);

        $this->assertContains($fields['message'], $line);
        $this->assertNull($label);
    }

    public function testFormatWithEmptyExtra()
    {
        $fields = array( 'message' => 'foo',
                'extra' => array() );

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
