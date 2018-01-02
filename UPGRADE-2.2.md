UPGRADE FROM 2.1 to 2.2
=======================

Dictionary\Operators
--------------------

 * add `nlist` operator. With this operator is requested a field returned
   must not be included in results. For example, to get every records of an
   entity except those whose ids are 42, 23 and 44 the query string should be:

   filtering[id|nlist]=42,23,44

   and all other ids will be returned.
