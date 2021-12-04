<?php

declare(strict_types=1);

namespace LaminasTest\Log\Filter;

use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Filter\Regex;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Log
 */
class RegexTest extends TestCase
{
    public function testMessageFilterRecognizesInvalidRegularExpression(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid reg');
        new Regex('invalid regexp');
    }

    public function testMessageFilter(): void
    {
        $filter = new Regex('/accept/');
        $this->assertTrue($filter->filter(['message' => 'foo accept bar']));
        $this->assertFalse($filter->filter(['message' => 'foo reject bar']));
    }
}
