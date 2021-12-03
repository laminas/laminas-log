<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

class StringObject
{
    public function __toString()
    {
        return 'Hello World';
    }
}
