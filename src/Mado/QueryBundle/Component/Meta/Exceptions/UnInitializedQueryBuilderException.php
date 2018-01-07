<?php

namespace Mado\QueryBundle\Component\Meta\Exceptions;
use Throwable;

/**
 * @since Class available since Release 2.2.0
 */
class UnInitializedQueryBuilderException extends \Exception {
    protected $customMessage = "Oops! Query builder was never initialized! call ::createQueryBuilder('entityName', 'alias') to start.";

    public function __construct()
    {
        parent::__construct($this->customMessage);
    }
}
