<?php

declare(strict_types=1);

namespace Laminas\Log\Formatter;

class FirePhp implements FormatterInterface
{
    /**
     * Formats the given event data into a single line to be written by the writer.
     *
     * @param  array $event The event data which should be formatted.
     * @return array line message and optionally label if 'extra' data exists.
     */
    public function format($event)
    {
        $label = null;
        if (! empty($event['extra'])) {
            $line  = $event['extra'];
            $label = $event['message'];
        } else {
            $line = $event['message'];
        }

        return [$line, $label];
    }

    /**
     * This method is implemented for FormatterInterface but not used.
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return '';
    }

    /**
     * This method is implemented for FormatterInterface but not used.
     *
     * @param  string             $dateTimeFormat
     * @return FormatterInterface
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        return $this;
    }
}
