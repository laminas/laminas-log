<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\TestAsset;

use Laminas\Log\Writer\AbstractWriter;

class ConcreteWriter extends AbstractWriter
{
    protected function doWrite(array $event)
    {
    }
}
