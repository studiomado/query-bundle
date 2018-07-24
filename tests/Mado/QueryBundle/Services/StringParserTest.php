<?php

use Mado\QueryBundle\Services\StringParser;
use PHPUnit\Framework\TestCase;

class StringParserTest extends TestCase
{
    public function testTransformSnakeCaseInLowerCamelCaseStrings()
    {
        $this->parser = new StringParser();

        $this->assertEquals(
            'fizzBuzzFooBar',
            $this->parser->camelize('fizz_buzz_foo_bar')
        );
    }

    public function testDotNotationFor()
    {
        $result = StringParser::dotNotationFor('Foo\bar');

        $this->assertEquals('foo.bar', $result);
    }
}
