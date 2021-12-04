<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Formatter;
use Laminas\Log\FormatterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;

class FormatterPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;
    use ServicesNotSharedByDefaultTrait;

    protected function getPluginManager()
    {
        return new FormatterPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return Formatter\FormatterInterface::class;
    }
}
