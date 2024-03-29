<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Writer\ChromePhp\ChromePhpInterface;

class MockChromePhp implements ChromePhpInterface
{
    public $calls = [];

    protected $enabled;

    public function __construct($enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function error($line)
    {
        $this->calls['error'][] = $line;
    }

    public function warn($line)
    {
        $this->calls['warn'][] = $line;
    }

    public function info($line)
    {
        $this->calls['info'][] = $line;
    }

    public function trace($line)
    {
        $this->calls['trace'][] = $line;
    }

    public function log($line)
    {
        $this->calls['log'][] = $line;
    }
}
