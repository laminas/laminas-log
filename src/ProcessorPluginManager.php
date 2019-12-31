<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager for log processors.
 */
class ProcessorPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'backtrace'      => Processor\Backtrace::class,
        'psrplaceholder' => Processor\PsrPlaceholder::class,
        'referenceid'    => Processor\ReferenceId::class,
        'requestid'      => Processor\RequestId::class,

        // Legacy Zend Framework aliases
        \Zend\Log\Processor\Backtrace::class => Processor\Backtrace::class,
        \Zend\Log\Processor\PsrPlaceholder::class => Processor\PsrPlaceholder::class,
        \Zend\Log\Processor\ReferenceId::class => Processor\ReferenceId::class,
        \Zend\Log\Processor\RequestId::class => Processor\RequestId::class,

        // v2 normalized FQCNs
        'zendlogprocessorbacktrace' => Processor\Backtrace::class,
        'zendlogprocessorpsrplaceholder' => Processor\PsrPlaceholder::class,
        'zendlogprocessorreferenceid' => Processor\ReferenceId::class,
        'zendlogprocessorrequestid' => Processor\RequestId::class,
    ];

    protected $factories = [
        Processor\Backtrace::class      => InvokableFactory::class,
        Processor\PsrPlaceholder::class => InvokableFactory::class,
        Processor\ReferenceId::class    => InvokableFactory::class,
        Processor\RequestId::class      => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminaslogprocessorbacktrace'      => InvokableFactory::class,
        'laminaslogprocessorpsrplaceholder' => InvokableFactory::class,
        'laminaslogprocessorreferenceid'    => InvokableFactory::class,
        'laminaslogprocessorrequestid'      => InvokableFactory::class,
    ];

    protected $instanceOf = Processor\ProcessorInterface::class;

    /**
     * Allow many processors of the same type (v2)
     * @param bool
     */
    protected $shareByDefault = false;

    /**
     * Allow many processors of the same type (v3)
     * @param bool
     */
    protected $sharedByDefault = false;

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($instance) ? get_class($instance) : gettype($instance))
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @throws InvalidServiceException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Processor\ProcessorInterface',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                __NAMESPACE__
            ));
        }
    }
}
