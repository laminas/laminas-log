<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

class SerializableObject
{
    public function __toString()
    {
        return self::class;
    }
}
