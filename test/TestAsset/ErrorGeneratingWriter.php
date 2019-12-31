<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Writer\AbstractWriter;

class ErrorGeneratingWriter extends AbstractWriter
{
    protected function doWrite(array $event)
    {
        $stream = fopen("php://memory", "r");
        fclose($stream);
        fwrite($stream, "test");
    }
}
