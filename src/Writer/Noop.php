<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

class Noop extends AbstractWriter
{
    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
    }
}
