<?php

use Mado\QueryBundle\Component\Meta\Dijkstra;
use PHPUnit\Framework\TestCase as TestCase;

class DijkstraTest extends TestCase
{
    public function testFindAllPossibilePath()
    {
        $this->samepleJson = [
            "AppBundle\\Entity\\a" => [
                "relations" => [
                    "item" => "AppBundle\\Entity\\Fizz",
                ]
            ],
            "AppBundle\\Entity\\mood" => [
                "relations" => [
                    "item" => "AppBundle\\Entity\\b",
                ]
            ],
            "AppBundle\\Entity\\Fizz" => [
                "relations" => [
                    "item" => "AppBundle\\Entity\\mood",
                ]
            ],
            "AppBundle\\Entity\\b" => [
                "relations" => [
                    "item" => "AppBundle\\Entity\\Fizz",
                    "icdsatem" => "AppBundle\\Entity\\a",
                ]
            ],
        ];

        $dijkstra = new Dijkstra($this->samepleJson);

        $paths = $dijkstra->shortestPaths(
            'AppBundle\\Entity\\a',
            'AppBundle\\Entity\\b',
            [
                'AppBundle\\Entity\\mood',
            ]
        );

        $this->assertEquals(
            [[
                'AppBundle\\Entity\\a',
                'AppBundle\\Entity\\Fizz',
                'AppBundle\\Entity\\mood',
                'AppBundle\\Entity\\b',
            ]],
            $paths
        );
    }
}
