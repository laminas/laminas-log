<?php

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Writer\AbstractWriter;

class ErrorGeneratingWriter extends AbstractWriter
{
    protected function doWrite(array $event)
    {
        trigger_error('test', E_USER_WARNING);
    }
}
