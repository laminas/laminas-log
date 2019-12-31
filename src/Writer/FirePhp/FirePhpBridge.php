<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

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
     *
     * @param  FirePHP $firephp
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
     * @param  string $line
     * @return void
     */
    public function error($line)
    {
        return $this->firephp->error($line);
    }

    /**
     * Log a warning
     *
     * @param  string $line
     * @return void
     */
    public function warn($line)
    {
        return $this->firephp->warn($line);
    }

    /**
     * Log informational message
     *
     * @param  string $line
     * @return void
     */
    public function info($line)
    {
        return $this->firephp->info($line);
    }

    /**
     * Log a trace
     *
     * @param  string $line
     * @return void
     */
    public function trace($line)
    {
        return $this->firephp->trace($line);
    }

    /**
     * Log a message
     *
     * @param  string $line
     * @return void
     */
    public function log($line)
    {
        return $this->firephp->trace($line);
    }
}
