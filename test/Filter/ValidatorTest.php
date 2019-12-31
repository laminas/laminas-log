<?php

/**
 * @see       https://github.com/laminas/laminas-log for the canonical source repository
 * @copyright https://github.com/laminas/laminas-log/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-log/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Validator;
use Laminas\Validator\Digits as DigitsFilter;
use Laminas\Validator\NotEmpty as NotEmptyFilter;
use Laminas\Validator\ValidatorChain;

/**
 * @group      Laminas_Log
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (! class_exists(ValidatorChain::class)) {
            $this->markTestSkipped(
                'laminas-validator related tests are disabled when testing laminas-servicemanager v3 '
                . 'forwards compatibility, until laminas-validator is also forwards compatible'
            );
        }
    }

    public function testValidatorFilter()
    {
        $filter = new Validator(new DigitsFilter());
        $this->assertTrue($filter->filter(['message' => '123']));
        $this->assertFalse($filter->filter(['message' => 'test']));
        $this->assertFalse($filter->filter(['message' => 'test123']));
        $this->assertFalse($filter->filter(['message' => '(%$']));
    }

    public function testValidatorChain()
    {
        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new NotEmptyFilter());
        $validatorChain->attach(new DigitsFilter());
        $filter = new Validator($validatorChain);
        $this->assertTrue($filter->filter(['message' => '123']));
        $this->assertFalse($filter->filter(['message' => 'test']));
    }
}
