<?php

use Mado\QueryBundle\Repositories\BaseRepository;
use PHPUnit\Framework\TestCase;

class BaseRepositoryTest extends TestCase
{
    public function testExtractEntityAliasFromClassName()
    {
        $this->manager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->meta = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->meta->name = '\Fully\Qualified\Class\Name';

        $repo = new BaseRepository(
            $this->manager,
            $this->meta
        );

        $this->assertEquals(
            'n',
            $repo->getEntityAlias()
        );
    }
}

