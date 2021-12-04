<?php

declare(strict_types=1);

namespace Laminas\Log\Processor;

use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function method_exists;
use function strpos;
use function strtr;

/**
 * Processes an event message according to PSR-3 rules.
 *
 * This processor replaces `{foo}` with the value from `$extra['foo']`.
 */
class PsrPlaceholder implements ProcessorInterface
{
    /**
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        if (false === strpos($event['message'], '{')) {
            return $event;
        }

        $replacements = [];
        foreach ($event['extra'] as $key => $val) {
            if (
                $val === null
                || is_scalar($val)
                || (is_object($val) && method_exists($val, "__toString"))
            ) {
                $replacements['{' . $key . '}'] = $val;
                continue;
            }

            if (is_object($val)) {
                $replacements['{' . $key . '}'] = '[object ' . get_class($val) . ']';
                continue;
            }

            $replacements['{' . $key . '}'] = '[' . gettype($val) . ']';
        }

        $event['message'] = strtr($event['message'], $replacements);
        return $event;
    }
}
