# QueryBundle

latest stable version [![Latest Stable Version](https://poser.pugx.org/studiomado/query-bundle/version)](https://packagist.org/packages/studiomado/query-bundle)


| 2.4 (master) | 2.3 | 2.2 |
|----------------|----------|---|
| [![Build Status](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/studiomado/query-bundle/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/build.png?b=2.3)](https://scrutinizer-ci.com/g/studiomado/query-bundle/build-status/2.3) | [![Build Status](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/build.png?b=2.2)](https://scrutinizer-ci.com/g/studiomado/query-bundle/build-status/2.2) |
| [![Code Coverage](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/coverage.png?b=2.3)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=2.3) | [![Code Coverage](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/coverage.png?b=2.2)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=2.2) |
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=master) |  [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/quality-score.png?b=2.3)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=2.3) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/quality-score.png?b=2.2)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=2.2) |


## Run tests

 - `./runtests.sh` run all unit tests
 - `./agile.sh` generate testdox documentation
 - `./coverage.sh` generate and open html coverage

# Plain symfony project for query-bundle

The purpose of this project is to see how studiomado/query-bundle works and can be installed in a plain symfony project.

 - database configuration
 - install query-bundle
 - create at least one entity

# Database configuration

Remember to update parameter.yml file, parameter.yml.dist and config.yml file. In config.yml file also remember that the drive MUST be changed in pdo_sqlite to enable doctrine to work with this database.

This is just an example: for this example we use sqlite but in production you can use mysql or postgres or any other database supported by doctrine.

    prompt> ./bin/console doctrine:database:create
    Created database /path/to/project/var/data/data.sqlite for connection named default

# Install query-bundle

    prompt> composer require studiomado/query-bundle

# Create at least one entity

Create at least one entity ...

    prompt> ./bin/console doctrine:generate:entity

In this example I created an entity Task following command steps.

    created ./src/AppBundle/Entity/
    created ./src/AppBundle/Entity/Task.php
    > Generating entity class src/AppBundle/Entity/Task.php: OK!
    > > Generating repository class src/AppBundle/Repository/TaskRepository.php: OK!

... and update the schema ...

		prompt> ./bin/console doctrine:schema:update
		ATTENTION: This operation should not be executed in a production environment.
							 Use the incremental update to detect changes during development and use
							 the SQL DDL provided to manually update your database in production.

		The Schema-Tool would execute "1" queries to update the database.
		Please run the operation by passing one - or both - of the following options:
				doctrine:schema:update --force to execute the command
				doctrine:schema:update --dump-sql to dump the SQL statements to the screen

The schema update works only with force option

    prompt> ./bin/console doctrine:schema:update --force
    Updating database schema...
    Database schema updated successfully! "1" query was executed

Just take a look of the database content (that now is simply empty).

    prompt> ./bin/console doctrine:query:dql "select t from AppBundle:Task t"

The query will return an empty array of result

     array (size=0)
       empty

Just add first task ... 

    prompt> ./bin/console doctrine:query:sql "insert into task values (null, 'complete this guide', 'todo') "

and take a look of the content

    prompt> ./bin/console doctrine:query:dql "select t from AppBundle:Task t"

    array (size=1)
      0 =>
        object(stdClass)[507]
          public '__CLASS__' => string 'AppBundle\Entity\Task' (length=21)
          public 'id' => int 1
          public 'description' => string 'complete this guide' (length=19)
          public 'status' => string 'todo' (length=4)


# Complete installation

First of all install vendors

    prompt> composer require jms/serializer-bundle
    prompt> composer require willdurand/hateoas-bundle
    prompt> composer require white-october/pagerfanta-bundle
    prompt> composer require friendsofsymfony/rest-bundle

and then, … add vendors in your app/AppKernel 

    new FOS\RestBundle\FOSRestBundle(),
    new JMS\SerializerBundle\JMSSerializerBundle(),
    new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),

# Complete configuration and use the bundle

Once everything is done, you can add new endpoints using the query-bundle to query the database.

```
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /** @Route("/", name="homepage") */
    public function indexAction(
        Request $request,
        \Doctrine\ORM\EntityManager $em,
        \JMS\Serializer\Serializer $serializer
    ) {
        $data = $em->getRepository('AppBundle:Task')
            ->setRequest($request)
            ->findAllPaginated();

        $content = $serializer->serialize($data, 'json');

        return new Response($content, 200);
    }
}
```

# Configure your entity repository

Now be sure that your repository extends the right BaseRepository.

```
namespace AppBundle\Repository;

class TaskRepository extends \Mado\QueryBundle\Repositories\BaseRepository
{
    // to do …
}
```

```
namespace AppBundle\Entity;

/** @ORM\Entity(repositoryClass="AppBundle\Repository\TaskRepository") */
class Task
{
    // to do …
}
```

# Customize entity serialization

Now if you want to customize responses add

    use JMS\Serializer\Annotation as JMS;

On top of your entities and complete your JMS configurations. See JMS documentation to get all the complete documentation.

Here some examples:

 - http://127.0.0.1:8000/?filtering[status]=todo
 - http://127.0.0.1:8000/?filtering[status|contains]=od
 - http://127.0.0.1:8000/?filtering[status|endswith]=gress

# Find All No Paginated

Added a new method in **BaseRepository**  
When you need results applying filter and sort without pagination
```
public function findAllNoPaginated();
```
This feature was needed to create an Excel Report, injecting results into the Excel Report

Example without pagination
--------------------------
In Controller:
```
public function getTasksExcelReportAction(Request $request)
    {
        $tasks = $this->getDoctrine()
            ->getRepository('AppBundle:Task')
            ->findAllNoPaginated();
        
        $reportExcel = new TasksReport($tasks);
        $reportExcel->createReport();
        
        $excelContent = $reportExcel->printReport();
        
        return new Response(
            $excelContent,
            200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );        
    } 
```  

Example with pagination
-----------------------
In Controller:
```
    public function getTasksAction(Request $request)
    {
        return $this->getDoctrine()
            ->getRepository('AppBundle:Task')
            ->setRequest($request)
            ->findAllPaginated();
    }
```

# Queries

## Or Conditions

If you want to create an or condition with this library you can create it from the client for example with a simple GET request like this:

```
/api/foo?filtering_or[name|eq]=bar&filtering_or[surname|eq]=bar
```

This request will produce a query like this:

```
SELECT f0_.id AS id_0, f0_.name AS name_1, f0_.surname AS surname_2" .
FROM foo f0_" .
WHERE ((f0_.name = "bar" OR f0_.surname = "bar"))
```

If you want instead to have more OR conditions separated you can do something like this:

 ```
 /api/foo?filtering_or[name|eq|1]=bar&filtering_or[surname|eq|1]=bar&filtering_or[group|contains|2]=baz&filtering_or[role|contains|2]=baz
 ```
 
This request will produce a query like this:

```
SELECT f0_.id AS id_0, f0_.name AS name_1, f0_.surname AS surname_2, f0_.group AS group_3, f0_.role AS role_4" .
FROM foo f0_" .
WHERE (f0_.name = "bar" OR f0_.surname = "bar") AND (f0_.group LIKE "%baz%" OR f0_.role LIKE "%baz%")
```

This can be done by using a counter after the operator separated by ```|``` 