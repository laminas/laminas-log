<?php

declare(strict_types=1);

namespace Laminas\Log\Formatter;

interface FormatterInterface
{
    /**
     * Default format specifier for DateTime objects is ISO 8601
     *
     * @see http://php.net/manual/en/function.date.php
     */
    public const DEFAULT_DATETIME_FORMAT = 'c';

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event event data
     * @return string|array Either a formatted line to write to the log, or the
     *     updated event information to provide to the writer.
     */
    public function format($event);

    /**
     * Get the format specifier for DateTime objects
     *
     * @return string
     */
    public function getDateTimeFormat();

    /**
     * Set the format specifier for DateTime objects
     *
     * @see http://php.net/manual/en/function.date.php
     *
     * @param string $dateTimeFormat DateTime format
     * @return FormatterInterface
     */
    public function setDateTimeFormat($dateTimeFormat);
}
