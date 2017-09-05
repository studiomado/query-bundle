[![Build Status](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/studiomado/query-bundle/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/studiomado/query-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/studiomado/query-bundle/?branch=master)
# Plain symfony project for query-bundle

The purpose of this project is to see how studiomado/query-bundle works and can be installed in a plain symfony project.

 - database configuration
 - install query-bundle
 - create at least one entity

# database configuration

Remember to update parameter.yml file, parameter.yml.dist and config.yml file. In config.yml file also remember that the drive MUST be changed in pdo_sqlite to enable doctrine to work with this database.

This is just an example: for this example we use sqlite but in production you can use mysql or postgres or any other database supported by doctrine.

    prompt> ./bin/console doctrine:database:create
    Created database /path/to/project/var/data/data.sqlite for connection named default

# Install query-bundle

    composer require studiomado/query-bundle

# Create at least one entity

Create at least one entity ...

    ./bin/console doctrine:generate:entity

In this example I created an entity Task following command steps.

    created ./src/AppBundle/Entity/
    created ./src/AppBundle/Entity/Task.php
    > Generating entity class src/AppBundle/Entity/Task.php: OK!
    > > Generating repository class src/AppBundle/Repository/TaskRepository.php: OK!

... and update the schema ...

		 ./bin/console doctrine:schema:update
		ATTENTION: This operation should not be executed in a production environment.
							 Use the incremental update to detect changes during development and use
							 the SQL DDL provided to manually update your database in production.

		The Schema-Tool would execute "1" queries to update the database.
		Please run the operation by passing one - or both - of the following options:
				doctrine:schema:update --force to execute the command
				doctrine:schema:update --dump-sql to dump the SQL statements to the screen

The schema update works only with force option

    ./bin/console doctrine:schema:update --force
    Updating database schema...
    Database schema updated successfully! "1" query was executed

Just take a look of the database content (that now is simply empty).

    ./bin/console doctrine:query:dql "select t from AppBundle:Task t"

The query will return an empty array of result

     array (size=0)
       empty

Just add first task ... 

    ./bin/console doctrine:query:sql "insert into task values (null, 'complete this guide', 'todo') "

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

    composer require jms/serializer-bundle
    composer require willdurand/hateoas-bundle
    composer require white-october/pagerfanta-bundle

and then, … add vendors in your app/AppKernel 

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

Now if you want to customize responses add

    use JSM\Serializer\Annotation as JMS;

on top of your entities and complete your JMS configurations. See JMS documentation to get all the complete documentation.

Here some examples:

 - http://127.0.0.1:8000/?filtering[status]=todo
 - http://127.0.0.1:8000/?filtering[status|contains]=od
 - http://127.0.0.1:8000/?filtering[status|endswith]=gress
