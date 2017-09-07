<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Objects\WhereCondition;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Objects\WhereCondition
 */
class WhereConditionTest extends TestCase
{
    /**
     * @covers ::setFiltering
     * @covers ::setOperator
     * @covers ::setSalt
     * @covers ::setFieldName
     * @covers ::setEntityAlias
     * @covers ::getCondition
     * @covers ::getFieldName
     */
    public function testCheckIfEntityFieldIsEqualToValue()
    {
        $this->salt = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Salt')
            ->disableOriginalConstructor()
            ->getMock();

        $this->operator = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Operator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->operator->expects($this->once())
            ->method('getMeta')
            ->will($this->returnValue('='));

        $this->filtering = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();

        $whereCondition = new WhereCondition();
        $whereCondition->setFiltering($this->filtering);
        $whereCondition->setOperator($this->operator);
        $whereCondition->setSalt($this->salt);
        $whereCondition->setFieldName('name');
        $whereCondition->setEntityAlias('alias');

        $this->assertSame('alias.name = :field_name', $whereCondition->getCondition());
    }

    /**
     * @covers ::setFiltering
     * @covers ::setOperator
     * @covers ::setSalt
     * @covers ::setFieldName
     * @covers ::setEntityAlias
     * @covers ::getCondition
     * @covers ::getFieldName
     * @covers ::getEmbeddedCondition
     * @covers ::isListOperator
     * @covers ::setRelationEntityAlias
     * @covers ::getListFieldName
     * @covers ::setValue
     */
    public function testCheckIfEntityFieldIsEqualToListOfValues()
    {
        $this->salt = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Salt')
            ->disableOriginalConstructor()
            ->getMock();
        $this->salt->expects($this->once())
            ->method('getSalt')
            ->will($this->returnValue('_44'));

        $this->operator = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Operator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->operator->expects($this->once())
            ->method('getMeta')
            ->will($this->returnValue('='));

        $this->filtering = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filtering->expects($this->once())
            ->method('hasOperator')
            ->will($this->returnValue(true));
        $this->filtering->expects($this->any())
            ->method('isListOperator')
            ->will($this->returnValue(true));

        $whereCondition = new WhereCondition();
        $whereCondition->setFiltering($this->filtering);
        $whereCondition->setOperator($this->operator);
        $whereCondition->setSalt($this->salt);
        $whereCondition->setFieldName('name');
        $whereCondition->setEntityAlias('relationAlias');
        $whereCondition->setValue('fooBar');

        $this->assertSame('relationAlias.name = (:field_name_44)', $whereCondition->getCondition());
    }

    /**
     * @covers ::setFiltering
     * @covers ::setOperator
     * @covers ::setSalt
     * @covers ::setFieldName
     * @covers ::setEntityAlias
     * @covers ::getCondition
     * @covers ::getFieldName
     * @covers ::getEmbeddedCondition
     * @covers ::isListOperator
     * @covers ::setRelationEntityAlias
     * @covers ::getListFieldName
     * @covers ::setValue
     */
    public function testCheckIfRelationPropertyIsEqualToValue()
    {
        $this->salt = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Salt')
            ->disableOriginalConstructor()
            ->getMock();
        $this->salt->expects($this->once())
            ->method('getSalt');

        $this->operator = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Operator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->operator->expects($this->any())
            ->method('getMeta')
            ->will($this->returnValue('='));

        $this->filtering = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filtering->expects($this->once())
            ->method('hasOperator')
            ->will($this->returnValue(true));
        $this->filtering->expects($this->any())
            ->method('isListOperator')
            ->will($this->returnValue(false));

        $whereCondition = new WhereCondition();
        $whereCondition->setFiltering($this->filtering);
        $whereCondition->setOperator($this->operator);
        $whereCondition->setSalt($this->salt);
        $whereCondition->setFieldName('name');
        $whereCondition->setEntityAlias('relationAlias');
        $whereCondition->setValue('fooBar');

        $this->assertSame('relationAlias.name = :field_name', $whereCondition->getCondition());
    }

    /**
     * @covers ::setFiltering
     * @covers ::setOperator
     * @covers ::setSalt
     * @covers ::setFieldName
     * @covers ::setEntityAlias
     * @covers ::getCondition
     * @covers ::getFieldName
     * @covers ::getEmbeddedCondition
     * @covers ::isListOperator
     * @covers ::setRelationEntityAlias
     * @covers ::getListFieldName
     */
    public function testCheckIfRelationPropertyIsEqualToListOfValues()
    {
        $this->salt = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Salt')
            ->disableOriginalConstructor()
            ->getMock();

        $this->operator = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Operator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->operator->expects($this->once())
            ->method('getMeta')
            ->will($this->returnValue('='));

        $this->filtering = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filtering->expects($this->once())
            ->method('isListOperator')
            ->will($this->returnValue(true));

        $whereCondition = new WhereCondition();
        $whereCondition->setFiltering($this->filtering);
        $whereCondition->setOperator($this->operator);
        $whereCondition->setSalt($this->salt);
        $whereCondition->setFieldName('name');
        $whereCondition->setRelationEntityAlias('relationAlias');

        $this->assertSame('relationAlias.name = (:field_name)', $whereCondition->getEmbeddedCondition());
    }


    /**
     * @covers ::setFiltering
     * @covers ::setOperator
     * @covers ::setSalt
     * @covers ::setFieldName
     * @covers ::setEntityAlias
     * @covers ::getCondition
     * @covers ::getFieldName
     * @covers ::getEmbeddedCondition
     * @covers ::isListOperator
     * @covers ::setRelationEntityAlias
     * @covers ::setValue
     */
    public function testCheckIfTwoFieldsHaveSameValue()
    {
        $this->salt = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Salt')
            ->disableOriginalConstructor()
            ->getMock();
        $this->operator = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\Operator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->operator->expects($this->once())
            ->method('getMeta')
            ->will($this->returnValue('='));

        $this->filtering = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filtering->expects($this->once())
            ->method('hasOperator')
            ->will($this->returnValue(true));
        $this->filtering->expects($this->once())
            ->method('isListOperator')
            ->will($this->returnValue(false));
        $this->filtering->expects($this->once())
            ->method('isFieldEqualsOperator')
            ->will($this->returnValue(true));

        $whereCondition = new WhereCondition();
        $whereCondition->setFiltering($this->filtering);
        $whereCondition->setOperator($this->operator);
        $whereCondition->setSalt($this->salt);
        $whereCondition->setFieldName('name');
        $whereCondition->setEntityAlias('relationAlias');
        $whereCondition->setValue('fooBar');

        $this->assertSame('relationAlias.name = relationAlias.fooBar', $whereCondition->getCondition());
    }
}
