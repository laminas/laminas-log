<?php

declare(strict_types=1);

namespace Laminas\Log\Filter;

use Laminas\Log\Exception;
use Traversable;

use function ctype_digit;
use function gettype;
use function is_array;
use function is_int;
use function iterator_to_array;
use function sprintf;
use function version_compare;

class Priority implements FilterInterface
{
    /** @var int */
    protected $priority;

    /** @var string */
    protected $operator;

    /**
     * Filter logging by $priority. By default, it will accept any log
     * event whose priority value is less than or equal to $priority.
     *
     * @param  int|array|Traversable $priority Priority
     * @param  string $operator Comparison operator
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($priority, $operator = null)
    {
        if ($priority instanceof Traversable) {
            $priority = iterator_to_array($priority);
        }
        if (is_array($priority)) {
            $operator = $priority['operator'] ?? null;
            $priority = $priority['priority'] ?? null;
        }
        if (! is_int($priority) && ! ctype_digit($priority)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Priority must be a number, received "%s"',
                gettype($priority)
            ));
        }

        $this->priority = (int) $priority;
        $this->operator = $operator ?? '<=';
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param array $event event data
     * @return bool accepted?
     */
    public function filter(array $event)
    {
        return version_compare((string) $event['priority'], (string) $this->priority, $this->operator);
    }
}
