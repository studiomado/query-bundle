<?php

namespace Mado\QueryBundle\Tests\Objects;

use Mado\QueryBundle\Objects\Operator;
use Mado\QueryBundle\Vocabulary\Operators;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Objects\Operator
 */
class OperatorTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getDefault
     * @covers ::getMeta
     */
    public function testIsEqualByDefault()
    {
        $op = Operator::getDefault();
        $this->assertEquals('=', $op->getMeta());
    }

    /**
     * @covers ::fromRawValue
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Raw operator must contain `meta` parameter
     */
    public function testCreationIsInvalidWithoutMetaParameter()
    {
        Operator::fromRawValue([
            // invalid raw value
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::fromRawValue
     * @covers ::getMeta
     */
    public function testCreationIsValidWithMetaParameter()
    {
        $op = Operator::fromRawValue([
            'meta' => '=',
        ]);

        $this->assertEquals(
            '=',
            $op->getMeta()
        );
    }

    /**
     * @covers ::fromRawValue
     * @covers ::__construct
     * @covers ::getSubstitutionPattern
     * @covers ::haveSubstitutionPattern
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Oops! Current operator have not substitution pattern.
     */
    public function testThrowExceptionIfSubstitutionPatternIsRequsted()
    {
        $op = Operator::fromRawValue([
            'meta' => '=',
        ]);

        $op->getSubstitutionPattern();
    }

    /**
     * @covers ::fromRawValue
     * @covers ::__construct
     * @covers ::getSubstitutionPattern
     * @covers ::haveSubstitutionPattern
     */
    public function testProvideSubstitutionPatternIfDefinedInConstructor()
    {
        $op = Operator::fromRawValue([
            'meta' => '=',
            'substitution_pattern' => 'foo',
        ]);

        $this->assertEquals(
            'foo',
            $op->getSubstitutionPattern()
        );
    }

    /**
     * @covers ::fromFilteringObject
     * @covers ::__construct
     * @covers ::getDefault
     */
    public function testReturnDefaultOperatorWheneverFilteringObjectHasNoOperator()
    {
        $this->filteringObject = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filteringObject->expects($this->once())
            ->method('hasOperator')
            ->will($this->returnValue(false));

        $op = Operator::fromFilteringObject($this->filteringObject);

        $this->assertEquals(
            $op,
            Operator::getDefault()
        );
    }

    /**
     * @covers ::fromFilteringObject
     * @covers ::__construct
     * @covers ::getDefault
     */
    public function testDontReturnDefaultOperatorWheneverFilteringObjectHasOperator()
    {
        $this->filteringObject = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filteringObject->expects($this->once())
            ->method('hasOperator')
            ->will($this->returnValue(true));
        $this->filteringObject->expects($this->once())
            ->method('getOperator')
            ->will($this->returnValue('list'));

        $op = Operator::fromFilteringObject($this->filteringObject);

        $this->assertNotEquals($op, Operator::getDefault());
    }

    /**
     * @covers ::fromFilteringObject
     * @covers ::__construct
     * @covers ::getDefault
     * @covers ::getRawValue
     */
    public function testProvideRawOperatorValue()
    {
        $this->filteringObject = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\FilteringObject')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filteringObject->expects($this->once())
            ->method('hasOperator')
            ->will($this->returnValue(true));
        $this->filteringObject->expects($this->once())
            ->method('getOperator')
            ->will($this->returnValue('list'));

        $op = Operator::fromFilteringObject($this->filteringObject);

        $operator = [
            'meta' => 'IN',
            'substitution_pattern' => '({string})',
        ];

        $operator = Operators::get('list');

        $this->assertEquals(
            $operator,
            $op->getRawValue()
        );
    }
}
