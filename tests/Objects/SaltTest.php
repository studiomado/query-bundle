<?php

use Mado\QueryBundle\Objects\Salt;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mado\QueryBundle\Objects\Salt
 */
class SaltTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::generateSaltForName
     * @covers ::getSalt
     */
    public function testIsEqualByDefault()
    {
        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue([
                new class { public function getName() {
                    return 'field_foo';
                }}
            ]));

        $salt = new Salt($this->queryBuilder);

        $salt->generateSaltForName('foo');

        $this->assertRegExp(
            '/_[0-9]{3}/',
            $salt->getSalt()
        );
    }
}
