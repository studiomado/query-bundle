<?php

namespace Mado\QueryBundle\Tests\Services;

use Mado\QueryBundle\Services\StringParser;
use PHPUnit\Framework\TestCase;

class StringParserTest extends TestCase
{
    public function setUp()
    {
        $this->parser = new StringParser();
    }

    /** @dataProvider tokens */
    public function testSplitStringInTokenViaUnderscore(
        int $numberOfTokens,
        string $string
    ) {
        $this->assertEquals(
            $numberOfTokens,
            $this->parser->numberOfTokens($string)
        );
    }

    /** @dataProvider tokens */
    public function testTo(
        int $numberOfTokens,
        string $string,
        int $tokenPosition,
        string $token
    ) {
        $this->assertEquals(
            $token,
            $this->parser->tokenize($string, $tokenPosition)
        );
    }

    public function tokens()
    {
        return [
            [1, 'foo', 0, 'foo'],
            [2, 'foo_bar', 0, 'foo'],
            [2, 'foo_bar', 1, 'bar'],
        ];
    }

    public function testCamelizeSnakeCaseToCamelCase()
    {
        $this->assertEquals(
            'lowerCamelCase',
            $this->parser->camelize('lower_camel_case')
        );
    }
}
