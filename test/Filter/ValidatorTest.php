<?php

namespace LaminasTest\Log\Filter;

use Laminas\Log\Filter\Validator;
use Laminas\Validator\Digits as DigitsFilter;
use Laminas\Validator\NotEmpty as NotEmptyFilter;
use Laminas\Validator\ValidatorChain;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidatorFilter(): void
    {
        $filter = new Validator(new DigitsFilter());
        $this->assertTrue($filter->filter(['message' => '123']));
        $this->assertFalse($filter->filter(['message' => 'test']));
        $this->assertFalse($filter->filter(['message' => 'test123']));
        $this->assertFalse($filter->filter(['message' => '(%$']));
    }

    public function testValidatorChain(): void
    {
        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new NotEmptyFilter());
        $validatorChain->attach(new DigitsFilter());
        $filter = new Validator($validatorChain);
        $this->assertTrue($filter->filter(['message' => '123']));
        $this->assertFalse($filter->filter(['message' => 'test']));
    }
}
