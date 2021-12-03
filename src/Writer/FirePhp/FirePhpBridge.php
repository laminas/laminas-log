<?php

declare(strict_types=1);

namespace Laminas\Log\Writer\FirePhp;

use FirePHP;

class FirePhpBridge implements FirePhpInterface
{
    /**
     * FirePHP instance
     *
     * @var FirePHP
     */
    protected $firephp;

    /**
     * Constructor
     */
    public function __construct(FirePHP $firephp)
    {
        $this->firephp = $firephp;
    }

    /**
     * Retrieve FirePHP instance
     *
     * @return FirePHP
     */
    public function getFirePhp()
    {
        return $this->firephp;
    }

    /**
     * Determine whether or not FirePHP is enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->firephp->getEnabled();
    }

    /**
     * Log an error message
     *
     * @param string $line
     * @param string|null $label
     * @return bool
     */
    public function error($line, $label = null)
    {
        return $this->firephp->error($line, $label);
    }

    /**
     * Log a warning
     *
     * @param  string      $line
     * @param  string|null $label
     * @return bool
     */
    public function warn($line, $label = null)
    {
        return $this->firephp->warn($line, $label);
    }

    /**
     * Log informational message
     *
     * @param  string      $line
     * @param  string|null $label
     * @return bool
     */
    public function info($line, $label = null)
    {
        return $this->firephp->info($line, $label);
    }

    /**
     * Log a trace
     *
     * @param  string $line
     * @return bool
     */
    public function trace($line)
    {
        return $this->firephp->trace($line);
    }

    /**
     * Log a message
     *
     * @param  string      $line
     * @param  string|null $label
     * @return bool
     */
    public function log($line, $label = null)
    {
        return $this->firephp->trace($line, $label);
    }
}
