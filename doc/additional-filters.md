# Additional Filters

Aims to limit information in response, based on predefined rules. Those rule
must be stored inside your application's user.

## User

User must respect `\Mado\QueryBundle\Interfaces\AdditionalFilterable` interface
and implement methods `getAdditionalFilters()`.

## Custom Filter

To work with additional filters, is necessary a custom filter. This should
respect the interface `Mado\QueryBundle\Interfaces\CustomFilter`.

### Example

You can create your custom filter using this syntax.

```php
<?php

namespace Mt\AdditionalFilters;

class YourCustomFilter implements CustomFilter
{
    private static $filterMap = [
        'filter_name' => \Bundle\To\EntityClass::class,
    ];

    public function __construct(
        EntityManagerInterface $manager,
        GraphWalker $dijkstraWalker,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->manager        = $manager;
        $this->dijkstraWalker = $dijkstraWalker;
        $this->requestStack   = $requestStack;
        $this->logger         = $logger;
    }

    public function setUser(AdditionalFilterable $user)
    {
        $this->additionalFilters = AdditionalFilterExtractor::fromUser($user);
        return $this;
    }

    public function allItemsTo(string $entity)
    {
        $this->entity = $entity;
        $filters = [];
        $translations = [
            'filter_name' => [
                'from' => '_embedded.shops.id',
                'to' => 'id',
            ],
        ];
        foreach ($translations as $filterName => $filterTranslation) {
            if ($this->additionalFilters->getFilters($filterName) != '') {
                $path = $this->getPathTo($filterName);
                $genericAdditionalFilter = Filter::box([
                    'ids'  => $this->additionalFilters->getFilters($filterName),
                    'path' => $path,
                ]);
                $filterKey = $genericAdditionalFilter->getFieldAndOperator();
                if ([] != $filterTranslation) {
                    if ($filterKey == $filterTranslation['from'] . '|' . $genericAdditionalFilter->getOperator()) {
                        $filterKey = $filterTranslation['to'] . '|' . $genericAdditionalFilter->getOperator();
                        $genericAdditionalFilter = $genericAdditionalFilter->withFullPath($filterKey);
                    }
                }
                $filtering = $this->requestStack->getCurrentRequest()->query->get('filtering', []);
                $haveCheckedAdditionalFilters = false;
                $field = $genericAdditionalFilter->getField();
                foreach( $filtering as $filterKey => $value) {
                    $genericQueryStringFilter = Filter::fromQueryStringFilter([
                        $filterKey =>  $value
                    ]);
                    if ($genericAdditionalFilter->getField() == $genericQueryStringFilter->getField()) {
                        if (
                            $genericAdditionalFilter->getOperator() == 'list'
                            && $genericAdditionalFilter->getOperator() == $genericQueryStringFilter->getOperator() 
                        ) {
                            $haveCheckedAdditionalFilters = true;
                            $additionalFiltersIds = explode(',', $genericAdditionalFilter->getIds());
                            $querystringIds = explode(',', $genericQueryStringFilter->getIds());
                            $intersection = array_intersect($querystringIds, $additionalFiltersIds);
                            $ids = join(',', $intersection);
                            if ($intersection == []) {
                                throw new ForbiddenContentException(
                                    'Oops! Forbidden requested id ' . $value
                                    . ' is not available. Available are '
                                    . $genericAdditionalFilter->getIds()
                                );
                            }
                            $filters[$genericAdditionalFilter->getFieldAndOperator()] = $ids;
                        }
                    }
                }
                if (!$haveCheckedAdditionalFilters) {
                    $ids = $genericAdditionalFilter->getIds();
                    $filters[$genericAdditionalFilter->getFieldAndOperator()] = $ids;
                }
            }
        }

        return $filters;
    }

    public function getPathTo(string $filter)
    {
        $this->dijkstraWalker->buildPathBetween(
            $this->entity,
            self::getEntityFromFilter($filter)
        );
        return $this->dijkstraWalker->getPath();
    }

    public static function getEntityFromFilter(string $filterName)
    {
        return self::$filterMap[$filterName];
    }

    public function setEntity(string $entity)
    {
        $this->entity = $entity;
        return $this;
    }
}
```
