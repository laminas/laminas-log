<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Writer\AbstractWriter;

class ConcreteWriter extends AbstractWriter
{
    protected function doWrite(array $event)
    {
    }
}
