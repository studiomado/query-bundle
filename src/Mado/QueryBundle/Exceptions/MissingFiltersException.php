<?php

namespace Mado\QueryBundle\Exceptions;

use Exception;

final class MissingFiltersException extends Exception
{
    protected $message = 'Not AND filters nor OR filters have been defined';
}
