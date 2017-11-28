UPGRADE FROM 2.0 to 2.1
=======================

UnreachablePathException
------------------------

 * An exception that's throwed when is not possibile to get the path.

UndefinedPathException
----------------------

 * An exception that's throwed when entities path is requested too early.

MapBuilder
----------

 * Build a map based on Doctrine's DataMapper.

DataMapper
----------

 * An interface to build maps of relations of database entities.

JsonPathFinder
--------------

 * Build the path of relations between entities.

```php
$finder = new JsonPathFinder($this->mapper);
$finder->setQueryStartEntity("FooBundle\\Entity\\Start");
$finder->getPathToEntity("AppBundle\\Entity\\End"); // _embedded.start.end
```
