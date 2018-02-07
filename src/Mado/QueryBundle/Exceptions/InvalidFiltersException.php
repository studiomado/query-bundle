<?php

namespace Mado\QueryBundle\Exceptions;

use Exception;
use Throwable;

final class InvalidFiltersException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
