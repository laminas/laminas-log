<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

class MockDbPlatform
{
    private array $calls;

    public function __call($method, $params)
    {
        $this->calls[$method][] = $params;
    }
}
