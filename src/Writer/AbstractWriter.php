<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

use Laminas\Log\Exception;
use Laminas\Log\Filter;
use Laminas\Log\FilterPluginManager as LogFilterPluginManager;
use Laminas\Log\Formatter;
use Laminas\Log\FormatterPluginManager as LogFormatterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ErrorHandler;
use Traversable;

use function get_class;
use function gettype;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function iterator_to_array;
use function sprintf;

use const E_WARNING;

/**
 * @todo Remove aliases for parent namespace's FilterPluginManager and
 *    FormatterPluginManager once the deprecated versions in the current
 *    namespace are removed (likely v3.0).
 */
abstract class AbstractWriter implements WriterInterface
{
    /**
     * Filter plugins
     *
     * @var FilterPluginManager
     */
    protected $filterPlugins;

    /**
     * Formatter plugins
     *
     * @var FormatterPluginManager
     */
    protected $formatterPlugins;

    /**
     * Filter chain
     *
     * @var Filter\FilterInterface[]
     */
    protected $filters = [];

    /**
     * Formats the log message before writing
     *
     * @var Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * Use Laminas\Stdlib\ErrorHandler to report errors during calls to write
     *
     * @var bool
     */
    protected $convertWriteErrorsToExceptions = true;

    /**
     * Error level passed to Laminas\Stdlib\ErrorHandler::start for errors reported during calls to write
     *
     * @var bool
     */
    protected $errorsToExceptionsConversionLevel = E_WARNING;

    /**
     * Constructor
     *
     * Set options for a writer. Accepted options are:
     * - filters: array of filters to add to this filter
     * - formatter: formatter for this writer
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        if (is_array($options)) {
            if (isset($options['filter_manager'])) {
                $this->setFilterPluginManager($options['filter_manager']);
            }

            if (isset($options['formatter_manager'])) {
                $this->setFormatterPluginManager($options['formatter_manager']);
            }

            if (isset($options['filters'])) {
                $filters = $options['filters'];
                if (is_int($filters) || is_string($filters) || $filters instanceof Filter\FilterInterface) {
                    $this->addFilter($filters);
                } elseif (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (is_int($filter) || is_string($filter) || $filter instanceof Filter\FilterInterface) {
                            $this->addFilter($filter);
                        } elseif (is_array($filter)) {
                            if (! isset($filter['name'])) {
                                throw new Exception\InvalidArgumentException(
                                    'Options must contain a name for the filter'
                                );
                            }
                            $filterOptions = $filter['options'] ?? null;
                            $this->addFilter($filter['name'], $filterOptions);
                        }
                    }
                }
            }

            if (isset($options['formatter'])) {
                $formatter = $options['formatter'];
                if (is_string($formatter) || $formatter instanceof Formatter\FormatterInterface) {
                    $this->setFormatter($formatter);
                } elseif (is_array($formatter)) {
                    if (! isset($formatter['name'])) {
                        throw new Exception\InvalidArgumentException('Options must contain a name for the formatter');
                    }
                    $formatterOptions = $formatter['options'] ?? null;
                    $this->setFormatter($formatter['name'], $formatterOptions);
                }
            }
        }
    }

    /**
     * Add a filter specific to this writer.
     *
     * @param  int|string|Filter\FilterInterface $filter
     * @param  array|null $options
     * @return AbstractWriter
     * @throws Exception\InvalidArgumentException
     */
    public function addFilter($filter, ?array $options = null)
    {
        if (is_int($filter)) {
            $filter = new Filter\Priority($filter);
        }

        if (is_string($filter)) {
            $filter = $this->filterPlugin($filter, $options);
        }

        if (! $filter instanceof Filter\FilterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Filter must implement %s\Filter\FilterInterface; received "%s"',
                __NAMESPACE__,
                is_object($filter) ? get_class($filter) : gettype($filter)
            ));
        }

        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Get filter plugin manager
     *
     * @return LogFilterPluginManager
     */
    public function getFilterPluginManager()
    {
        if (null === $this->filterPlugins) {
            $this->setFilterPluginManager(new LogFilterPluginManager(new ServiceManager()));
        }
        return $this->filterPlugins;
    }

    /**
     * Set filter plugin manager
     *
     * @param  string|LogFilterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFilterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins();
        }
        if (! $plugins instanceof LogFilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer plugin manager must extend %s; received %s',
                LogFilterPluginManager::class,
                is_object($plugins) ? get_class($plugins) : gettype($plugins)
            ));
        }

        $this->filterPlugins = $plugins;
        return $this;
    }

    /**
     * Get filter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Filter\FilterInterface
     */
    public function filterPlugin($name, ?array $options = null)
    {
        return $this->getFilterPluginManager()->get($name, $options);
    }

    /**
     * Get formatter plugin manager
     *
     * @return LogFormatterPluginManager
     */
    public function getFormatterPluginManager()
    {
        if (null === $this->formatterPlugins) {
            $this->setFormatterPluginManager(new LogFormatterPluginManager(new ServiceManager()));
        }
        return $this->formatterPlugins;
    }

    /**
     * Set formatter plugin manager
     *
     * @param  string|LogFormatterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFormatterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins();
        }
        if (! $plugins instanceof LogFormatterPluginManager) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Writer plugin manager must extend %s; received %s',
                    LogFormatterPluginManager::class,
                    is_object($plugins) ? get_class($plugins) : gettype($plugins)
                )
            );
        }

        $this->formatterPlugins = $plugins;
        return $this;
    }

    /**
     * Get formatter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Formatter\FormatterInterface
     */
    public function formatterPlugin($name, ?array $options = null)
    {
        return $this->getFormatterPluginManager()->get($name, $options);
    }

    /**
     * Log a message to this writer.
     *
     * @param array $event log data event
     * @return void
     */
    public function write(array $event)
    {
        foreach ($this->filters as $filter) {
            if (! $filter->filter($event)) {
                return;
            }
        }

        $errorHandlerStarted = false;

        if ($this->convertWriteErrorsToExceptions && ! ErrorHandler::started()) {
            ErrorHandler::start($this->errorsToExceptionsConversionLevel);
            $errorHandlerStarted = true;
        }

        try {
            $this->doWrite($event);
        } catch (\Exception $e) {
            if ($errorHandlerStarted) {
                ErrorHandler::stop();
            }
            throw $e;
        }

        if ($errorHandlerStarted) {
            $error = ErrorHandler::stop();
            if ($error) {
                throw new Exception\RuntimeException("Unable to write", 0, $error);
            }
        }
    }

    /**
     * Set a new formatter for this writer
     *
     * @param  string|Formatter\FormatterInterface $formatter
     * @param  array|null $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFormatter($formatter, ?array $options = null)
    {
        if (is_string($formatter)) {
            $formatter = $this->formatterPlugin($formatter, $options);
        }

        if (! $formatter instanceof Formatter\FormatterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Formatter must implement %s\Formatter\FormatterInterface; received "%s"',
                __NAMESPACE__,
                is_object($formatter) ? get_class($formatter) : gettype($formatter)
            ));
        }

        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Get formatter
     *
     * @return Formatter\FormatterInterface
     */
    protected function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Check if the writer has a formatter
     *
     * @return bool
     */
    protected function hasFormatter()
    {
        return $this->formatter instanceof Formatter\FormatterInterface;
    }

    /**
     * Set convert write errors to exception flag
     *
     * @param bool $convertErrors
     */
    public function setConvertWriteErrorsToExceptions($convertErrors)
    {
        $this->convertWriteErrorsToExceptions = $convertErrors;
    }

    /**
     * Perform shutdown activities such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {
    }

    /**
     * Write a message to the log
     *
     * @param array $event log data event
     * @return void
     */
    abstract protected function doWrite(array $event);
}
