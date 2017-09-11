<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Objects\RequestOptions;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Objects\RequestOptions
 */
class RequestOptionsTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::fromRequest
     * @covers ::asArray
     */
    public function testFromFilterWithDefaultValue()
    {
        $this->attributeParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeParameterBag
            ->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->queryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag
            ->method('get')
            ->will($this->returnValue([
                // the collection of attributesd
            ]));

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->attributes = $this->attributeParameterBag;
        $this->request->query = $this->queryParameterBag;

        $ro = RequestOptions::fromRequest($this->request);

        $requestOptionsExpected = [];

        $requestOptionsExpected['_route'] = [];
        $requestOptionsExpected['customer_id'] = [];
        $requestOptionsExpected['id'] = [];
        $requestOptionsExpected['filters'] = [];
        $requestOptionsExpected['orFiltering'] = [];
        $requestOptionsExpected['sorting '] = [];
        $requestOptionsExpected['printing'] = [];
        $requestOptionsExpected['rel'] = [];
        $requestOptionsExpected['page'] = [];
        $requestOptionsExpected['select'] = [];
        $requestOptionsExpected['filtering'] = [];
        $requestOptionsExpected['limit'] = [];

        $this->assertEquals($requestOptionsExpected, $ro->asArray());
    }

    /**
     * @covers ::__construct
     * @covers ::fromRequest
     * @covers ::asArray
     */
    public function testFromFilterWithValue()
    {
        $route = 'route ' . rand(0, 1000);
        $customerId = 'customer_id ' . rand(0, 1000);
        $id = 'id ' . rand(0, 1000);
        $filters = 'filters ' . rand(0 ,1000);
        $orFilterings = 'orFiltering ' . rand(0 ,1000);
        $sorting = 'sorting ' . rand(0 ,1000);
        $printing = 'printing ' . rand(0 ,1000);
        $rel = 'rel ' . rand(0 ,1000);
        $page = 'page ' . rand(0 ,1000);
        $select = 'select ' . rand(0 ,1000);
        $filtering = 'filtering ' . rand(0 ,1000);
        $limit = 'limit ' . rand(0 ,1000);

        $this->attributeParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeParameterBag
            ->expects($this->at(0))
            ->method('get')
            ->with('_route')
            ->will($this->returnValue([
                $route,
            ]));

        $this->attributeParameterBag
            ->expects($this->at(1))
            ->method('get')
            ->with('customer_id')
            ->will($this->returnValue([
                 $customerId,
            ]));

        $this->attributeParameterBag
            ->expects($this->at(2))
            ->method('get')
            ->with('id')
            ->will($this->returnValue([
                $id,
            ]));

        $this->queryParameterBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParameterBag
            ->expects($this->at(0))
            ->method('get')
            ->with('filtering')
            ->will($this->returnValue([
                $filters,
            ]));

        $this->queryParameterBag
            ->expects($this->at(1))
            ->method('get')
            ->with('filtering_or')
            ->will($this->returnValue([
                $orFilterings,
            ]));

        $this->queryParameterBag
            ->expects($this->at(2))
            ->method('get')
            ->with('sorting')
            ->will($this->returnValue([
                $sorting,
            ]));

        $this->queryParameterBag
            ->expects($this->at(3))
            ->method('get')
            ->with('printing')
            ->will($this->returnValue([
                $printing,
            ]));

        $this->queryParameterBag
            ->expects($this->at(4))
            ->method('get')
            ->with('rel')
            ->will($this->returnValue([
                $rel,
            ]));

        $this->queryParameterBag
            ->expects($this->at(5))
            ->method('get')
            ->with('page')
            ->will($this->returnValue([
                $page,
            ]));

        $this->queryParameterBag
            ->expects($this->at(6))
            ->method('get')
            ->with('select')
            ->will($this->returnValue([
                $select,
            ]));

        $this->queryParameterBag
            ->expects($this->at(7))
            ->method('get')
            ->with('filtering')
            ->will($this->returnValue([
                $filtering,
            ]));

        $this->queryParameterBag
            ->expects($this->at(8))
            ->method('get')
            ->with('limit')
            ->will($this->returnValue([
                $limit,
            ]));

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->attributes = $this->attributeParameterBag;
        $this->request->query = $this->queryParameterBag;

        $ro = RequestOptions::fromRequest($this->request);

        $requestOptionsExpected = [];

        $requestOptionsExpected['_route'] = [$route];
        $requestOptionsExpected['customer_id'] = [$customerId];
        $requestOptionsExpected['id'] = [$id];
        $requestOptionsExpected['filters'] = [$filters];
        $requestOptionsExpected['orFiltering'] = [$orFilterings];
        $requestOptionsExpected['sorting '] = [$sorting];
        $requestOptionsExpected['printing'] = [$printing];
        $requestOptionsExpected['rel'] = [$rel];
        $requestOptionsExpected['page'] = [$page];
        $requestOptionsExpected['select'] = [$select];
        $requestOptionsExpected['filtering'] = [$filtering];
        $requestOptionsExpected['limit'] = [$limit];

        $this->assertEquals($requestOptionsExpected, $ro->asArray());
    }
}
