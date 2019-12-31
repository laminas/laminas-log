<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

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
