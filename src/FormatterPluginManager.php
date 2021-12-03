<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class FormatterPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'base'             => Formatter\Base::class,
        'simple'           => Formatter\Simple::class,
        'xml'              => Formatter\Xml::class,
        'db'               => Formatter\Db::class,
        'errorhandler'     => Formatter\ErrorHandler::class,
        'exceptionhandler' => Formatter\ExceptionHandler::class,

        // Legacy Zend Framework aliases
        //phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
        \Zend\Log\Formatter\Base::class             => Formatter\Base::class,
        \Zend\Log\Formatter\Simple::class           => Formatter\Simple::class,
        \Zend\Log\Formatter\Xml::class              => Formatter\Xml::class,
        \Zend\Log\Formatter\Db::class               => Formatter\Db::class,
        \Zend\Log\Formatter\ErrorHandler::class     => Formatter\ErrorHandler::class,
        \Zend\Log\Formatter\ExceptionHandler::class => Formatter\ExceptionHandler::class,
        //phpcs:enable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName

        // v2 normalized FQCNs
        'zendlogformatterbase'             => Formatter\Base::class,
        'zendlogformattersimple'           => Formatter\Simple::class,
        'zendlogformatterxml'              => Formatter\Xml::class,
        'zendlogformatterdb'               => Formatter\Db::class,
        'zendlogformattererrorhandler'     => Formatter\ErrorHandler::class,
        'zendlogformatterexceptionhandler' => Formatter\ExceptionHandler::class,
    ];

    protected $factories = [
        Formatter\Base::class             => InvokableFactory::class,
        Formatter\Simple::class           => InvokableFactory::class,
        Formatter\Xml::class              => InvokableFactory::class,
        Formatter\Db::class               => InvokableFactory::class,
        Formatter\ErrorHandler::class     => InvokableFactory::class,
        Formatter\ExceptionHandler::class => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminaslogformatterbase'             => InvokableFactory::class,
        'laminaslogformattersimple'           => InvokableFactory::class,
        'laminaslogformatterxml'              => InvokableFactory::class,
        'laminaslogformatterdb'               => InvokableFactory::class,
        'laminaslogformattererrorhandler'     => InvokableFactory::class,
        'laminaslogformatterexceptionhandler' => InvokableFactory::class,
    ];

    protected $instanceOf = Formatter\FormatterInterface::class;

    /**
     * Allow many formatters of the same type (v2)
     *
     * @param bool
     */
    protected $shareByDefault = false;

    /**
     * Allow many formatters of the same type (v3)
     *
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
                static::class,
                $this->instanceOf,
                is_object($instance) ? get_class($instance) : gettype($instance)
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @throws Exception\InvalidArgumentException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Formatter\FormatterInterface',
                is_object($plugin) ? get_class($plugin) : gettype($plugin),
                __NAMESPACE__
            ));
        }
    }
}
