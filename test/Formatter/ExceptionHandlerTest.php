<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Formatter;

use DateTime;
use Laminas\Log\Formatter\ExceptionHandler;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    public function testFormat()
    {
        $date = new DateTime();

        $event = [
            'timestamp'    => $date,
            'message'      => 'test',
            'priority'     => 1,
            'priorityName' => 'CRIT',
            'extra' => [
                'file'  => 'test.php',
                'line'  => 1,
                'trace' => [
                    [
                        'file'     => 'test.php',
                        'line'     => 1,
                        'function' => 'test',
                        'class'    => 'Test',
                        'type'     => '::',
                        'args'     => [1]
                    ],
                    [
                        'file'     => 'test.php',
                        'line'     => 2,
                        'function' => 'test',
                        'class'    => 'Test',
                        'type'     => '::',
                        'args'     => [1]
                    ]
                ]
            ]
        ];

        // The formatter ends with unix style line endings so make sure we expect that
        // output as well:
        $expected = $date->format('c') . " CRIT (1) test in test.php on line 1\n";
        $expected .= "[Trace]\n";
        $expected .= "File  : test.php\n";
        $expected .= "Line  : 1\n";
        $expected .= "Func  : test\n";
        $expected .= "Class : Test\n";
        $expected .= "Type  : static\n";
        $expected .= "Args  : Array\n";
        $expected .= "(\n";
        $expected .= "    [0] => 1\n";
        $expected .= ")\n\n";
        $expected .= "File  : test.php\n";
        $expected .= "Line  : 2\n";
        $expected .= "Func  : test\n";
        $expected .= "Class : Test\n";
        $expected .= "Type  : static\n";
        $expected .= "Args  : Array\n";
        $expected .= "(\n";
        $expected .= "    [0] => 1\n";
        $expected .= ")\n\n";

        $formatter = new ExceptionHandler();
        $output = $formatter->format($event);

        $this->assertEquals($expected, $output);
    }

    /**
     * @dataProvider provideDateTimeFormats
     */
    public function testSetDateTimeFormat($dateTimeFormat)
    {
        $date = new DateTime();

        $event = [
            'timestamp'    => $date,
            'message'      => 'test',
            'priority'     => 1,
            'priorityName' => 'CRIT',
            'extra' => [
                'file'  => 'test.php',
                'line'  => 1,
            ],
        ];

        $expected = $date->format($dateTimeFormat) . ' CRIT (1) test in test.php on line 1';

        $formatter = new ExceptionHandler();

        $this->assertSame($formatter, $formatter->setDateTimeFormat($dateTimeFormat));
        $this->assertEquals($dateTimeFormat, $formatter->getDateTimeFormat());
        $this->assertEquals($expected, $formatter->format($event));
    }

    public function provideDateTimeFormats()
    {
        return [
            ['r'],
            ['U'],
        ];
    }
}
