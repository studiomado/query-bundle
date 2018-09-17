<?php

namespace Mado\QueryBundle\Objects;

class HalResponse
{
    private $json;

    private function __construct(array $json)
    {
        $this->json = $json;
    }

    public static function fromJson(string $json)
    {
        return self::fromArray(json_decode($json, true));
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
