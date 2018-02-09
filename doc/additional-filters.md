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
class YourCustomeFilter implements CustomFilter
{
    private $manager;

    private $dijkstraWalker;

    private $logger;

    private $filter;

    private $entity;

    private $requestStack;

    private $idsChecker;

    private static $domainilterToDomainEntityMap = [
      'filtername' => \Path\To\YourEntity::class,
    ];

    public function __construct(
        EntityManagerInterface $manager,
        GraphWalker $dijkstraWalker,
        RequestStack $requestStack,
        IdsChecker $idsChecker,
        LoggerInterface $logger
    ) {
        $this->manager        = $manager;
        $this->dijkstraWalker = $dijkstraWalker;
        $this->requestStack   = $requestStack;
        $this->idsChecker     = $idsChecker;
        $this->logger         = $logger;
    }

    public function setUser(AdditionalFilterable $user)
    {
        $this->filter = FilterExtractor::fromUser($user);
        return $this;
    }

    public function allItemsTo(string $entity)
    {
        $this->entity = $entity;
        $filters = [];
        $translations = [
          'filtername' => [],
        ];

        foreach ($translations as $filterName => $filterTranslation) {
            if ($this->filter->getFilters($filterName) != '') {
                $genericFilter = Filter::box([
                    'ids'  => $this->filter->getFilters($filterName),
                    'path' => $this->getPathTo($filterName),
                ]);

                $filterKey = $genericFilter->getRawFilter();
                $this->idsChecker->setFilterKey($filterKey);

                $afWithOperator = $this->filter->getFilters($filterName);
                $afOperator = $genericFilter->getOperator();
                $additionalFiltersIds = join(',', $afWithOperator[$afOperator]);
                $filtering = $this->requestStack
                    ->getCurrentRequest()
                    ->query
                    ->get('filtering', []);
                $rawFilteredIds = $genericFilter->getIds();
                $idsMustBeSubset = true;
                $this->idsChecker->setObjectFilter($genericFilter);
                $this->idsChecker->setFiltering($filtering);
                $this->idsChecker->validateIds();

                $filters[$this->idsChecker->getFilterKey()] = $this->idsChecker->getFinalFilterIds();
            }
        }

        return $filters;
    }

    public function getPathTo(string $domainFilter)
    {
        $domainEntity = self::getEntityFromFilter($domainFilter);
        $this->dijkstraWalker->buildPathBetween(
            $this->entity,
            $domainEntity
        );
        return $this->dijkstraWalker->getPath();
    }

    public static function getEntityFromFilter(string $filterName)
    {
        return self::$domainilterToDomainEntityMap[$filterName];
    }

    public function setEntity(string $entity)
    {
        $this->entity = $entity;
        return $this;
    }
}
```

### Create a service to query database with additional filters

```php
final class QueryBuilder
{
    private $domainFilter;

    private $storage;

    private $token;

    private $user;

    private $request;

    private $logger;

    public function __construct(
        YourCustomeFilter $domainFilter,
        TokenStorage $storage,
        EntityManager $manager,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->domainFilter = $domainFilter;
        $this->storage   = $storage;
        $this->manager   = $manager;
        $this->request   = $requestStack->getCurrentRequest();
        $this->logger    = $logger;

        $this->token = $this->storage->getToken();
        $this->user  = $this->token->getUser();
    }

    public function getRepository(string $rootEntity)
    {
        $this->rootEntity = $rootEntity;

        $additionalFilters = $this->domainFilter
            ->setUser($this->user)
            ->allItemsTo($rootEntity);

        return $this->manager
            ->getRepository($this->rootEntity)
            ->setRequestWithFilter(
                $this->request,
                $additionalFilters
            );
    }
}
```

### Define services in your application

```yaml
YourApplication\YourCustomeFilter:
    class: YourApplication\YourCustomeFilter
    public: true
    arguments:
        - '@doctrine.orm.entity_manager'
        - '@mado.dijkstrawalker'
        - '@request_stack'
        - '@mado.idscheker'
        - '@logger'

Application\Services\QueryBuilder:
    class: AppBundle\Services\QueryBuilder
    public: true

mado.dijkstrawalker:
    public: true
    class: Mado\QueryBundle\Component\Meta\DijkstraWalker
    arguments:
        - '@mado.mapper'

mado.idscheker:
    public: true
    class: Mado\QueryBundle\Services\IdsChecker

mado.mapper:
    public: true
    class: Mado\QueryBundle\Component\Meta\MapBuilder
```

### Call QueryBuilder inside the controller

```php
return $this
    ->get(\Application\Services\QueryBuilder::class)
    ->getRepository(\YourBundle\Entity\EntityToSearch::class)
    ->findAllPaginated();
```
