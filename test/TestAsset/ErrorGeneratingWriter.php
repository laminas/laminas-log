<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Writer\AbstractWriter;

use function trigger_error;

use const E_USER_WARNING;

class ErrorGeneratingWriter extends AbstractWriter
{
    protected function doWrite(array $event)
    {
        trigger_error('test', E_USER_WARNING);
    }
}
