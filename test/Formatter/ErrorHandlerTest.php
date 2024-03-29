<?php

declare(strict_types=1);

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\ErrorHandler;
use LaminasTest\Log\TestAsset\NotStringObject;
use LaminasTest\Log\TestAsset\StringObject;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function testFormat(): void
    {
        $date = new DateTime();

        $event     = [
            'timestamp'    => $date,
            'message'      => 'test',
            'priority'     => 1,
            'priorityName' => 'CRIT',
            'extra'        => [
                'errno'   => 1,
                'file'    => 'test.php',
                'line'    => 1,
                'context' => ['object' => new DateTime(), 'string' => 'test'],
            ],
        ];
        $formatter = new ErrorHandler();
        $output    = $formatter->format($event);

        $this->assertEquals($date->format('c') . ' CRIT (1) test (errno 1) in test.php on line 1', $output);
    }

    public function testSetDateTimeFormat(): void
    {
        $formatter = new ErrorHandler();

        $this->assertEquals('c', $formatter->getDateTimeFormat());
        $this->assertSame($formatter, $formatter->setDateTimeFormat('r'));
        $this->assertEquals('r', $formatter->getDateTimeFormat());
    }

    public function testComplexEvent(): void
    {
        $date         = new DateTime();
        $stringObject = new StringObject();
        $event        = [
            'timestamp'    => $date,
            'message'      => 'test',
            'priority'     => 1,
            'priorityName' => 'CRIT',
            'extra'        => [
                'errno'   => 1,
                'file'    => 'test.php',
                'line'    => 1,
                'context' => [
                    'object1' => new StringObject(),
                    'object2' => new NotStringObject(),
                    'string'  => 'test1',
                    'array'   => [
                        'key' => 'test2',
                    ],
                ],
            ],
        ];
        $formatString = '%extra[context][object1]% %extra[context][object2]% %extra[context][string]% '
            . '%extra[context][array]% %extra[context][array][key]%';
        $formatter    = new ErrorHandler($formatString);
        $output       = $formatter->format($event);
        $this->assertEquals(
            $stringObject->__toString() . ' %extra[context][object2]% test1 %extra[context][array]% test2',
            $output
        );
    }
}
