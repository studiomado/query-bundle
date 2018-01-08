<?php

namespace Mado\QueryBundle\Component\Meta\Exceptions;
use Throwable;

/**
 * @since Class available since Release 2.2
 */
class UnInitializedQueryBuilderException extends \Exception
{
    protected $message = "Oops! Query builder was never initialized! call ::createQueryBuilder('entityName', 'alias') to start.";
}
