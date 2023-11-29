<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Writer;
use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Traversable;

class WriterPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;
    use ServicesNotSharedByDefaultTrait;

    protected static function getPluginManager(): AbstractPluginManager
    {
        return new WriterPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return Writer\WriterInterface::class;
    }

    /**
     * Overrides CommonPluginManagerTrait::aliasProvider
     *
     * Iterates through aliases, and for adapters that require extensions,
     * tests if the extension is loaded, skipping that alias if not.
     *
     * @return Traversable
     */
    public function aliasProvider()
    {
        $pluginManager = $this->getPluginManager();
        $r             = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            switch ($target) {
                case Writer\Mail::class:
                    // intentionally fall-through
                case Writer\Db::class:
                    // intentionally fall-through
                case Writer\FingersCrossed::class:
                    // intentionally fall-through
                case Writer\Mongo::class:
                    // intentionally fall-through
                case Writer\MongoDB::class:
                    // intentionally fall-through
                case Writer\Stream::class:
                    // always skip; these implementations have required arguments
                    break;
                default:
                    yield $alias => [$alias, $target];
            }
        }
    }
}
