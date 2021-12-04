<?php

declare(strict_types=1);

namespace Laminas\Log\Formatter;

interface LogFormatterProviderInterface
{
    /**
     * Provide plugin manager configuration for log formatters.
     *
     * @return array
     */
    public function getLogFormatterConfig();
}
