<?php

use PHPUnit\Framework\TestCase;

class BaseRepositoryTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Oops! QueryBuilderOptions is missing
     */
    public function testOsterone()
    {
        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new \Mado\QueryBundle\Repositories\BaseRepository(
            $this->manager,
            $this->classMetadata
        );

        $obj->getQueryBuilderFactory();
    }

    public function testMetaDataAdapterConfigure()
    {
        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new \Mado\QueryBundle\Repositories\BaseRepository(
            $this->manager,
            $this->classMetadata
        );

        $classMetadata = [];
        $this->repository = $this
            ->getMockBuilder('Mado\QueryBundle\Repositories\BaseRepository')
            ->disableOriginalConstructor()
            ->setMethods([
                'getClassMetadata',
                'getEntityName',
            ])
            ->getMock();
        $this->repository->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);
        $this->repository->expects($this->once())
            ->method('getEntityName')
            ->willReturn('foo');

        $this->metadata = $this
            ->getMockBuilder('Mado\QueryBundle\Objects\MetaDataAdapter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata->expects($this->once())
            ->method('setClassMetadata')
            ->with($classMetadata);
        $this->metadata->expects($this->once())
            ->method('setEntityName')
            ->with('foo');

        $obj->__invoke(
            $this->metadata,
            $this->repository
        );
    }
}
