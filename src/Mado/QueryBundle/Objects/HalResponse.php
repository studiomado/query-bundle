<?php

namespace Mado\QueryBundle\Objects;

class HalResponse
{
    private $json;

    private function __construct(array $json)
    {
        $this->json = $json;
    }

    public static function fromArray(array $json)
    {
        return new self($json);
    }

    public function total()
    {
        return $this->json['total'];
    }
}
