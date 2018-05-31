CHANGELOG for 2.2
=================

This changelog references the relevant changes (bug and security fixes) done
in 2.2 minor versions.

 - add new [FilterObject] component
 - undefined index list
 - anonymous method `setFilters` to more explicit `setAndFilters`
 - remove `Mado\QueryBundle\Queries\Objects\Operator` statement
 - remove variable assignment 'cause its unused
 - convert negative limit to PHP_INT_MAX
 - add [Services\FilterExtractor] extract additional filters from AdditionalFilterable
 - add [Objects\Filter] to read filter value
 - add [AdditionalFilterable] interface
