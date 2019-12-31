<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log\Formatter;

use DateTime;
use Laminas\Log\Exception;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage Formatter
 */
class ErrorHandler extends Simple
{
    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%) %message% (errno %extra[errno]%) in %extra[file]% on line %extra[line]%';

    /**
     * This method formats the event for the PHP Error Handler.
     *
     * @param  array $event
     * @return string
     */
    public function format($event)
    {
        $output = $this->format;

        if (isset($event['timestamp']) && $event['timestamp'] instanceof DateTime) {
            $event['timestamp'] = $event['timestamp']->format($this->getDateTimeFormat());
        }

        foreach ($event as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $sname => $svalue) {
                    $output = str_replace("%{$name}[{$sname}]%", $svalue, $output);
                }
            } else {
                $output = str_replace("%$name%", $value, $output);
            }
        }

        return $output;
    }
}
