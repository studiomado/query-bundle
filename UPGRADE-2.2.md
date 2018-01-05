UPGRADE FROM 2.1 to 2.2
=======================

## Deprecations

\Dictionary\Operators
---------------------

 * Marked as deprecated. It will be removed in version 2.3.

\Queries\Objects\Operator
-------------------------

 * This component is now marked as deprecated and will be removed in version
   2.3.

## Enhancements

\Dictionary
-----------

 * Old Dictionary\Operators is now moved here in `Mado\QueryBundle\Dictionary`.

 * Add `nlist` operator. With this operator is requested a field returned
   must not be included in results. For example, to get every records of an
   entity except those whose ids are 42, 23 and 44 the query string should be:

   filtering[id|nlist]=42,23,44

   and all other ids will be returned.

\Queries\Objects\FilterObject
-----------------------------

 * This new component take te responsibility to manage a filtering option. For
   example, inside the `filtering[foo|bar]=42` query, FilterObject aims to
   manage the `foo|bar` part. It knows field name and operator name.
