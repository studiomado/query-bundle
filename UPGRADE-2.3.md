UPGRADE FROM 2.2 to 2.3
=======================

Dictionary\Operators
--------------------

 * The `componet` was removed.

Queries\QueryBuilderFactory
---------------------------

 * Use `setAndFilters` instead of `setFilters`

 * `QueryBuilderFactory::setRel()` now accept only arrays and relations are
   always stored as array.
   
 * Deprecate `customQueryStringValues` so is unnecessary to overwrite It inside entities 
