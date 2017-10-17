<?php

use Mado\QueryBundle\Services\StringParser;
use PHPUnit\Framework\TestCase;

class StringParserTest extends TestCase
{
    public function testTransformSnakeCaseStringInTokens()
    {
        $this->parser = new StringParser();

        $this->assertEquals(
            'fizzBuzzFooBar',
            $this->parser->camelize('fizz_buzz_foo_bar')
        );
    }
}
