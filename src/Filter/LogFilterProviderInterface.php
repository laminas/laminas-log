<?php

declare(strict_types=1);

namespace Laminas\Log\Filter;

interface LogFilterProviderInterface
{
    /**
     * Provide plugin manager configuration for log filters.
     *
     * @return array
     */
    public function getLogFilterConfig();
}
