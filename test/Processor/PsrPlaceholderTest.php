<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Processor;

use Laminas\Log\Processor\PsrPlaceholder;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversDefaultClass Laminas\Log\Processor\PsrPlaceholder
 */
class PsrPlaceholderTest extends TestCase
{
    /**
     * @dataProvider pairsProvider
     * @covers ::process
     */
    public function testReplacement($val, $expected)
    {
        $psrProcessor = new PsrPlaceholder;
        $event = $psrProcessor->process([
            'message' => '{foo}',
            'extra'   => ['foo' => $val]
        ]);
        $this->assertEquals($expected, $event['message']);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function pairsProvider()
    {
        return [
            'string'     => ['foo', 'foo'],
            'string-int' => ['3', '3'],
            'int'        => [3, '3'],
            'null'       => [null, ''],
            'true'       => [true, '1'],
            'false'      => [false, ''],
            'stdclass'   => [new stdClass, '[object stdClass]'],
            'array'      => [[], '[array]'],
        ];
    }
}
