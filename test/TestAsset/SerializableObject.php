<?php

namespace LaminasTest\Log\TestAsset;

class SerializableObject
{
    public function __toString()
    {
        return __CLASS__;
    }
}
