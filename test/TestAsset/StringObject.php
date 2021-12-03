<?php

namespace LaminasTest\Log\TestAsset;

class StringObject
{
    public function __toString()
    {
        return 'Hello World';
    }
}
