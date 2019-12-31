<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log;

use Laminas\Log\WriterPluginManager;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class WriterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriterPluginManager
     */
    protected $plugins;

    public function setUp()
    {
        $this->plugins = new WriterPluginManager();
    }

    public function testRegisteringInvalidWriterRaisesException()
    {
        $this->setExpectedException('Laminas\Log\Exception\InvalidArgumentException', 'must implement');
        $this->plugins->setService('test', $this);
    }

    public function testInvokableClassFirephp()
    {
        $firephp = $this->plugins->get('firephp');
        $this->assertInstanceOf('Laminas\Log\Writer\Firephp', $firephp);
    }
}
