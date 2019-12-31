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
 * Plugin manager for log writers.
 */
class WriterPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'chromephp'      => Writer\ChromePhp::class,
        'db'             => Writer\Db::class,
        'fingerscrossed' => Writer\FingersCrossed::class,
        'firephp'        => Writer\FirePhp::class,
        'mail'           => Writer\Mail::class,
        'mock'           => Writer\Mock::class,
        'noop'           => Writer\Noop::class,
        'psr'            => Writer\Psr::class,
        'stream'         => Writer\Stream::class,
        'syslog'         => Writer\Syslog::class,
        'zendmonitor'    => Writer\ZendMonitor::class,

        // The following are for backwards compatibility only; users
        // should update their code to use the noop writer instead.
        'null'              => Writer\Noop::class,
        Writer\Null::class  => Writer\Noop::class,
        'laminaslogwriternull' => Writer\Noop::class,

        // Legacy Zend Framework aliases
        \Zend\Log\Writer\ChromePhp::class => Writer\ChromePhp::class,
        \Zend\Log\Writer\Db::class => Writer\Db::class,
        \Zend\Log\Writer\FirePhp::class => Writer\FirePhp::class,
        \Zend\Log\Writer\Mail::class => Writer\Mail::class,
        \Zend\Log\Writer\Mock::class => Writer\Mock::class,
        \Zend\Log\Writer\Noop::class => Writer\Noop::class,
        \Zend\Log\Writer\Psr::class => Writer\Psr::class,
        \Zend\Log\Writer\Stream::class => Writer\Stream::class,
        \Zend\Log\Writer\Syslog::class => Writer\Syslog::class,
        \Zend\Log\Writer\FingersCrossed::class => Writer\FingersCrossed::class,
        \Zend\Log\Writer\ZendMonitor::class => Writer\ZendMonitor::class,
        \Zend\Log\Writer\Null::class => Writer\Noop::class,
        'zendlogwriternull' => Writer\Noop::class,

        // v2 normalized FQCNs
        'zendlogwriterchromephp' => Writer\ChromePhp::class,
        'zendlogwriterdb' => Writer\Db::class,
        'zendlogwriterfirephp' => Writer\FirePhp::class,
        'zendlogwritermail' => Writer\Mail::class,
        'zendlogwritermock' => Writer\Mock::class,
        'zendlogwriternoop' => Writer\Noop::class,
        'zendlogwriterpsr' => Writer\Psr::class,
        'zendlogwriterstream' => Writer\Stream::class,
        'zendlogwritersyslog' => Writer\Syslog::class,
        'zendlogwriterfingerscrossed' => Writer\FingersCrossed::class,
        'zendlogwriterzendmonitor' => Writer\ZendMonitor::class,

    ];

    protected $factories = [
        Writer\ChromePhp::class      => InvokableFactory::class,
        Writer\Db::class             => InvokableFactory::class,
        Writer\FirePhp::class        => InvokableFactory::class,
        Writer\Mail::class           => InvokableFactory::class,
        Writer\Mock::class           => InvokableFactory::class,
        Writer\Noop::class           => InvokableFactory::class,
        Writer\Psr::class            => InvokableFactory::class,
        Writer\Stream::class         => InvokableFactory::class,
        Writer\Syslog::class         => InvokableFactory::class,
        Writer\FingersCrossed::class => InvokableFactory::class,
        Writer\ZendMonitor::class    => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminaslogwriterchromephp'      => InvokableFactory::class,
        'laminaslogwriterdb'             => InvokableFactory::class,
        'laminaslogwriterfirephp'        => InvokableFactory::class,
        'laminaslogwritermail'           => InvokableFactory::class,
        'laminaslogwritermock'           => InvokableFactory::class,
        'laminaslogwriternoop'           => InvokableFactory::class,
        'laminaslogwriterpsr'            => InvokableFactory::class,
        'laminaslogwriterstream'         => InvokableFactory::class,
        'laminaslogwritersyslog'         => InvokableFactory::class,
        'laminaslogwriterfingerscrossed' => InvokableFactory::class,
        'laminaslogwriterzendmonitor'    => InvokableFactory::class,
    ];

    protected $instanceOf = Writer\WriterInterface::class;

    /**
     * Allow many writers of the same type (v2)
     * @param bool
     */
    protected $shareByDefault = false;

    /**
     * Allow many writers of the same type (v3)
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
                'Plugin of type %s is invalid; must implement %s\Writer\WriterInterface',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                __NAMESPACE__
            ));
        }
    }
}
