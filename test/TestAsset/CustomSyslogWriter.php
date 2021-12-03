<?php

declare(strict_types=1);

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Formatter\FormatterInterface;
use Laminas\Log\Writer\Syslog as SyslogWriter;

class CustomSyslogWriter extends SyslogWriter
{
    public function getFacility()
    {
        return $this->facility;
    }

    public function getApplicationName()
    {
        return $this->appName;
    }

    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
