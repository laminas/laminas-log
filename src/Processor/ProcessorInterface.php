<?php

declare(strict_types=1);

namespace Laminas\Log\Processor;

interface ProcessorInterface
{
    /**
     * Processes a log message before it is given to the writers
     *
     * @param  array $event
     * @return array
     */
    public function process(array $event);
}
