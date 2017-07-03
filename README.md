# `QueryBuilderFactory`

## Utilizzo normale

Queste sono le istruzioni per utilzzare il QueryBuilderFactory al posto dei normali repository. Questo QueryBuilderFactory ha la capacita' di leggere, ordinare, filtrare i dati. Puo' essere utilizzato all'interno di un normale repository ma anche standalone.

Quelle che seguono sono le compoenenti del BaseRepository che ci interessano. Tutti i repository dovranno estendere questo BaseRepository.

```php
class BaseRepository
{
    private $queryOptions;

    // …

    public function setRequest(Request $request)
    {
        return $this->setQueryOptionsFromRequest($request);
    }

    // …

    public function setQueryOptions(QueryBuilderOptions $options)
    {
        $this->queryOptions = $options;
    }

    // …

    public function setQueryOptionsFromRequest(Request $request = null)
    {
        $filters = $request->query->get('filtering', []);
        $sorting = $request->query->get('sorting', []);
        $printing = $request->query->get('printing', []);
        $rel = $request->query->get('rel', '');
        $page = $request->query->get('page', '');
        $select = $request->query->get('select', $this->entityAlias);
        $pageLength = $request->query->get('limit', 666);
        $filtering = $request->query->get('filtering', '');
        $limit = $request->query->get('limit', '');

        $this->queryOptions = QueryBuilderOptions::fromArray([
            // for pagination
            '_route' => $request->attributes->get('_route'),
            'filtering' => $filtering,
            'limit' => $limit,
            'page' => $page,
            'filters' => $filters,
            'sorting' => $sorting,
            'rel' => $rel,
            'printing' => $printing,
            'select' => $select,

            // for querystrings
            'sva_id' => $request->attributes->get('sva_id'),
            'customer_id' => $request->attributes->get('customer_id'),
            'id' => $request->attributes->get('id'),
        ]);

        return $this;
    }

    public function findAllPaginated()
    {
        $this->initFromQueryBuilderOptions($this->queryOptions);

        $this->queryBuilderFactory->filter();
        $this->queryBuilderFactory->sort();

        return $this->paginateResults($this->queryBuilderFactory->getQueryBuilder());
    }
}
```

Dentro ad una action, dovremo richiamare due metodi nuovi: `setRequest($request)` e `findAllPaginated()`. Il nuovo repository recuperera' le informazioni dalla richiesta e li paginera' per mostrarli all'utente finale.

```php
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class FooController extends FOSRestController
{
    // …

    public function getSvasAction(Request $request)
    {
        return $this->getDoctrine()
            ->getRepository('SvaBundle:Sva')
            ->setRequest($request)
            ->findAllPaginated();
    }

    // …
}
```

## Query piu' complicate

Quando in un repository si richiama un altro repository, non si ha la richiesta quindi bisogna passare a quella query le informazioni necessarie a filtrare ed ordinare i dati:

```php
public function findAllItems(Sva $sva)
{
    $itemRepository = $this->getEntityManager()->getRepository('SvaBundle:Item');

    $itemRepository->setQueryOptions($this->queryOptions);

    return $itemRepository->findAllBySva($sva);
}
```

E nell'altro bundle … Questo e' necessario perche' le join devono essere eseguite con un QueryBuilder di doctrine.

```php
public function findAllBySva(Sva $sva)
{
    $queryBuilderFactory = $this->getQueryBuilderFactory()
        ->filter()
        ->sort();

    $doctrineQueryBuilder = $queryBuilderFactory->getQueryBuilder()
        ->join($this->entityAlias.".sva", "s", "WITH", "s.id = :sva")
        ->setParameter("sva", $sva);

    return $this->paginateResults($doctrineQueryBuilder);
}
```
