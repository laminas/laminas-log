<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Regex;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Log
 */
class RegexTest extends TestCase
{
    public function testMessageFilterRecognizesInvalidRegularExpression()
    {
        $this->expectException('Laminas\Log\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('invalid reg');
        new Regex('invalid regexp');
    }

    public function testMessageFilter()
    {
        $filter = new Regex('/accept/');
        $this->assertTrue($filter->filter(['message' => 'foo accept bar']));
        $this->assertFalse($filter->filter(['message' => 'foo reject bar']));
    }
}
