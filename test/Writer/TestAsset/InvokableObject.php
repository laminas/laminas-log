<?php

declare(strict_types=1);

namespace LaminasTest\Log\Writer\TestAsset;

class InvokableObject
{
    /** @var array */
    public $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
}
