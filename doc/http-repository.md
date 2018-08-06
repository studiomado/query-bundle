# HttpRepository

For semplicity, a special repository can be injected inside a controller. The
repository is called HttpRepository because it works with http request. The
collaboration is implicit and trasparent for developer.

The component aims to build repository starting from its entity.

The `HttpRepository` provides just a method to get the query and another one to
keep the results.

## Controller

```php
<?php

namespace App\Controller;

use Mado\QueryBundle\Repositories\HttpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QueryBundleController extends Controller
{
    /** @Route("/query/bundle", name="query_bundle") */
    public function index(HttpRepository $httpRepository)
    {
        $httpRepository->buildForEntity(
            \App\Entity\Initiatives::class
        );

        return new JsonResponse([
            'query' => $httpRepository->getSql(),
            'res' => $httpRepository->getResult(),
        ]);
    }
}
```

## Response

Here an example with just two items for Initiatives's entity.

```json
{
  "query":"SELECT i0_.id AS id_0 FROM initiatives i0_ WHERE i0_.id IN (?)",
  "res":[
    {
      "id":1
    },
    {
      "id":2
    }
  ]
}
```
