<?php

namespace LaminasTest\Log\TestAsset;

use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Db\ResultSet\ResultSetInterface;

class MockDbAdapter extends DbAdapter
{
    public $plaftorm;
    public $driver;

    public $calls = [];

    public function __call($method, $params)
    {
        $this->calls[$method][] = $params;
    }

    public function __construct()
    {
        $this->platform = new MockDbPlatform;
        $this->driver = new MockDbDriver;
    }

    public function query(
        $sql,
        $parametersOrQueryMode = DbAdapter::QUERY_MODE_PREPARE,
        ResultSetInterface $resultPrototype = null
    ) {
        $this->calls[__FUNCTION__][] = $sql;
        return $this;
    }
}
