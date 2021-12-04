<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

use function trigger_error;

use const E_USER_WARNING;

/**
 * chmod() override for emulating warnings in tests.
 *
 * Set `$GLOBALS['chmod_throw_error']` to a truthy value in order to emulate
 * raising an E_WARNING.
 *
 * @param string $filename
 * @param int $mode
 * @return bool
 */
function chmod($filename, $mode)
{
    if (! empty($GLOBALS['chmod_throw_error'])) {
        trigger_error('some_error', E_USER_WARNING);
        return false;
    }

    return \chmod($filename, $mode);
}
