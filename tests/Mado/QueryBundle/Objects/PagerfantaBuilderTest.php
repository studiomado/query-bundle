<?php

use PHPUnit\Framework\TestCase;

class PagerfantaBuilderTest extends TestCase
{
    private $pagerfantaFactory;

    private $ormAdapter;

    private $pagerfantaBuilder;

    public function testCreatePaginatedRepresentationUsingHateoas()
    {
        $paginatedRepresentation = $this
            ->getMockBuilder('Hateoas\Representation\PaginatedRepresentation')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerfantaFactory = $this
            ->getMockBuilder('Hateoas\Representation\Factory\PagerfantaFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerfantaFactory
            ->expects($this->exactly(1))
            ->method('createRepresentation')
            ->willReturn($paginatedRepresentation);

        $this->ormAdapter = $this
            ->getMockBuilder('Pagerfanta\Adapter\DoctrineORMAdapter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerfantaBuilder = new \Mado\QueryBundle\Objects\PagerfantaBuilder(
            $this->pagerfantaFactory,
            $this->ormAdapter
        );

        $route = $this
            ->getMockBuilder('Hateoas\Configuration\Route')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pagerfantaBuilder->createRepresentation($route, random_int(0, 9999), random_int(0, 9999));
    }
}
