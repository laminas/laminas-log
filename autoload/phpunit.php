<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

/**
 * This is required due to the fact that we extend the LoggerInterfaceTest from psr/log, which
 * is still using non-namespaced versions of PHPUnit.
 */
if (! class_exists(\PHPUnit_Framework_TestCase::class)) {
    class_alias(\PHPUnit\Framework\TestCase::class, \PHPUnit_Framework_TestCase::class, true);
}
