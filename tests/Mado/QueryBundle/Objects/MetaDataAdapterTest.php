<?php

use Mado\QueryBundle\Objects\MetaDataAdapter;
use PHPUnit\Framework\TestCase;

class MetaDataAdapterTest extends TestCase
{
    public function setUp()
    {
        $this->adapter = new MetaDataAdapter();
    }

    public function testProvideEntityAliasFromEntityName()
    {
        $this->adapter->setEntityName('Foo\\Bar');
        $this->assertEquals('b', $this->adapter->getEntityAlias());
    }

    public function testProvideEntityFieldsFromMetadata()
    {
        $metadata = new \stdClass();
        $metadata->fieldMappings = [
            'foo' => 'fizz',
            'bar' => 'buzz',
        ];

        $this->adapter->setClassMetadata($metadata);
        $this->assertEquals([
            'foo',
            'bar',
        ], $this->adapter->getFields());
    }
}
