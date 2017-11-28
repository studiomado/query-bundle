<?php

namespace Mado\QueryBundle\Component\Meta;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Psr\Log\LoggerInterface;

/**
 * @since Class available since Release 2.1.0
 */
class MapBuilder implements DataMapper
{
    private $manager;

    private $map = [];

    private $logger;

    public function __construct(
        EntityManager $manager,
        LoggerInterface $logger = null
    ) {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function getMap() : array
    {
        if (!$this->map) {
            $this->rebuildRelationMap();
        }

        return $this->map;
    }

    public function forceCache(array $map)
    {
        $this->map = $map;
    }

    /** @codeCoverageIgnore */
    public static function relations(
        ClassMetadata $classMetadata,
        LoggerInterface $logger = null
    ) {
        $encoded = json_encode($classMetadata);
        $decoded = json_decode($encoded, true);
        $relations = $decoded['associationMappings'];

        $relMap = [];

        foreach ($relations as $name => $meta) {
            $relMap[$name] = $meta['targetEntity'];
        }

        return $relMap;
    }

    public function rebuildRelationMap()
    {
        $allMetadata = $this->manager
            ->getMetadataFactory()
            ->getAllMetadata();

        foreach ($allMetadata as $singleEntityMetadata) {
            // @codeCoverageIgnoreStart
            $this->map[$singleEntityMetadata->getName()]['relations'] = self::relations(
                $singleEntityMetadata,
                $this->logger
            );
            // @codeCoverageIgnoreEnd
        }
    }
}
