<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Regex;

/**
 * @group      Laminas_Log
 */
class RegexTest extends \PHPUnit_Framework_TestCase
{
    public function testMessageFilterRecognizesInvalidRegularExpression()
    {
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'invalid reg');
        new Regex('invalid regexp');
    }

    public function testMessageFilter()
    {
        $filter = new Regex('/accept/');
        $this->assertTrue($filter->filter(array('message' => 'foo accept bar')));
        $this->assertFalse($filter->filter(array('message' => 'foo reject bar')));
    }
}
