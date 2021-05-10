<?php

namespace LaminasTest\Log\TestAsset;

class MockDbPlatform
{

    public function __call($method, $params)
    {
        $this->calls[$method][] = $params;
    }
}
