<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\I18n\Validator\Int;
use Laminas\Log\Filter\Validator;
use Laminas\Validator\Digits as DigitsFilter;
use Laminas\Validator\ValidatorChain;

/**
 * @category   Laminas
 * @package    Laminas_Log
 * @subpackage UnitTests
 * @group      Laminas_Log
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatorFilter()
    {
        $filter = new Validator(new DigitsFilter());
        $this->assertTrue($filter->filter(array('message' => '123')));
        $this->assertFalse($filter->filter(array('message' => 'test')));
        $this->assertFalse($filter->filter(array('message' => 'test123')));
        $this->assertFalse($filter->filter(array('message' => '(%$')));
    }

    public function testValidatorChain()
    {
        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new DigitsFilter());
        $validatorChain->attach(new Int());
        $filter = new Validator($validatorChain);
        $this->assertTrue($filter->filter(array('message' => '123')));
        $this->assertFalse($filter->filter(array('message' => 'test')));
    }
}
