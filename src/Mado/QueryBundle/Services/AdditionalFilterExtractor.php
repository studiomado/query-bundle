<?php

namespace Mado\QueryBundle\Services;

use Mado\QueryBundle\Interfaces\AdditionalFilterable;

final class AdditionalFilterExtractor
{
    private $additionalFilters;

    private function __construct(AdditionalFilterable $user)
    {
        $this->additionalFilters = $user->getAdditionalFilters();
    }

    public static function fromUser(AdditionalFilterable $user)
    {
        return new self($user);
    }

    public function getFilters(string $filterName)
    {
        if (isset($this->additionalFilters[$filterName])) {
            return $this->additionalFilters[$filterName];
        }

        return '';
    }
}
