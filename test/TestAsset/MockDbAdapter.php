<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\TestAsset;

use Laminas\Db\Adapter\Adapter as DbAdapter;

class MockDbAdapter extends DbAdapter
{
    public $plaftorm;
    public $driver;

    public $calls = array();

    public function __call($method, $params)
    {
        $this->calls[$method][] = $params;
    }

    public function __construct()
    {
        $this->platform = new MockDbPlatform;
        $this->driver = new MockDbDriver;

    }
    public function query($sql, $parametersOrQueryMode = DbAdapter::QUERY_MODE_PREPARE)
    {
        $this->calls[__FUNCTION__][] = $sql;
        return $this;
    }
}
