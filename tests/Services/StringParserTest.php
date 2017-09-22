<?php

namespace Mado\QueryBundle\Tests\Services;

use Mado\QueryBundle\Services\StringParser;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Services\StringParser
 */
class StringParserTest extends TestCase
{
    public function setUp()
    {
        $this->parser = new StringParser();
    }

    /**
     * @covers \Mado\QueryBundle\Services\StringParser::numberOfTokens
     * @covers \Mado\QueryBundle\Services\StringParser::exploded
     * @covers Mado\QueryBundle\Services\StringParser::numberOfTokens
     * @covers Mado\QueryBundle\Services\StringParser::tokenize
     * @dataProvider tokens
     */
    public function testSplitStringInTokenViaUnderscore(
        int $numberOfTokens,
        string $string
    ) {
        $this->assertEquals(
            $numberOfTokens,
            $this->parser->numberOfTokens($string)
        );
    }

    /**
     * @covers \Mado\QueryBundle\Services\StringParser::tokenize
     * @covers \Mado\QueryBundle\Services\StringParser::exploded
     * @covers Mado\QueryBundle\Services\StringParser::numberOfTokens
     * @covers Mado\QueryBundle\Services\StringParser::tokenize
     * @dataProvider tokens
     */
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

    /**
     * @covers \Mado\QueryBundle\Services\StringParser::camelize
     * @covers \Mado\QueryBundle\Services\StringParser::exploded
     * @covers Mado\QueryBundle\Services\StringParser::numberOfTokens
     * @covers Mado\QueryBundle\Services\StringParser::tokenize
     * @dataProvider tokens
     */
    public function testCamelizeSnakeCaseToCamelCase()
    {
        $this->assertEquals(
            'lowerCamelCase',
            $this->parser->camelize('lower_camel_case')
        );
    }
}
