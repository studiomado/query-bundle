UPGRADE FROM 2.2 to 2.3
=======================

Dictionary\Operators
--------------------

 * The `componet` was removed.

\Queries\Objects\FilterObject
-----------------------------

 * This new component take te responsibility to manage a filtering option. For
   example, inside the `filtering[foo|bar]=42` query, FilterObject aims to
   manage the `foo|bar` part. It knows field name and operator name.

\Queries\Objects\Operator
-------------------------

 * This component is now marked as deprecated and will be removed in version
   2.4.
