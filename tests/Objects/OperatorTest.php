<?php

use Mado\QueryBundle\Objects\Operator;
use Mado\QueryBundle\Vocabulary\Operators;
use PHPUnit\Framework\TestCase;

class OperatorTest extends TestCase
{
    public function testIsEqualByDefault()
    {
        $op = Operator::getDefault();
        $this->assertEquals('=', $op->getMeta());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Raw operator must contain `meta` parameter
     */
    public function testCreationIsInvalidWithoutMetaParameter()
    {
        Operator::fromRawValue([
            // invalid raw value
        ]);
    }

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
