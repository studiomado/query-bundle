<?php

namespace Mado\QueryBundle\Component\Meta;

/**
 * @since Class available since Release 2.1.0
 */
class JsonPathFinder
{
    const INDEX_ENTITY_PARENT = 0;

    const INDEX_FK_RELATION_NAME = 1;

    const INDEX_ENTITY_FIRST_CHILD = 2;

    private $map;

    private $entity;

    private $entitiesPath = [];

    private $wrongPath = [];

    private $mapper;

    private $appendRootEntityToSubject;

    private $incrementSubject;

    private static $indeToDescriptionMap = [
        self::INDEX_ENTITY_PARENT      => 'parent',
        self::INDEX_FK_RELATION_NAME   => 'relation',
        self::INDEX_ENTITY_FIRST_CHILD => 'first child',
    ];

    public function __construct(
        DataMapper $mapper
    ) {
        $this->mapper = $mapper;

        $this->appendRootEntityToSubject =  function($subject, $rootEntity) {
            $subject[] = $rootEntity;
            return $subject;
        };

        $this->incrementSubject = function($subject) {
            return ++$subject;
        };
    }

    public function setEntity(string $entity)
    {
        $this->entity = $entity;
    }

    public function getFirstParentOf(string $innerEntity)
    {
        $this->getMap();

        return $this->keep(
            self::INDEX_ENTITY_PARENT,
            $innerEntity
        );
    }

    public function getFirstChildOf(string $innerEntity)
    {
        return $this->keep(
            self::INDEX_ENTITY_FIRST_CHILD,
            $innerEntity
        );
    }

    public function getSourceRelation(string $innerEntity)
    {
        return $this->keep(
            self::INDEX_FK_RELATION_NAME,
            $innerEntity
        );
    }

    public function clearMap(string $innerEntity)
    {
        if (in_array($this->entity, $this->listOfParentsOf($innerEntity))) {
            foreach ($this->map as $rootEntity => $meta) {
                if ($this->entity != $rootEntity) {
                    unset($this->map[$rootEntity]);
                }
            }
        }
    }

    public function getPathTo(string $innerEntity = '')
    {
        $this->entitiesPath[] = $innerEntity;

        $path = $this->getSourceRelation($innerEntity);

        if ($this->numberOfRelationsToEntity($innerEntity) != 1) {
            $this->clearMap($innerEntity);
        }

        if ($this->entity != $this->getFirstParentOf($innerEntity)) {
            if (!($relation = $this->getFirstParentOf($innerEntity))) {
                throw new Exceptions\UnreachablePathException(var_export([
                    'innerEntity' => $innerEntity,
                    'relation' => $relation,
                ], true));
            }

            return $this->getPathTo($relation) . '.' . $path;
        }

        return $path;
    }

    public function setQueryStartEntity(string $startEntity)
    {
        $this->setEntity($startEntity);
    }

    public function getPathToEntity(string $entityToReach)
    {
        foreach ($this->getMap() as $rootEntity => $meta) {
            if (in_array($rootEntity, $this->wrongPath)) {
                unset($this->map[$rootEntity]);
            }
        }

        return '_embedded.' . $this->getPathTo($entityToReach);
    }

    public function keep($val, $innerEntity)
    {
        foreach ($this->getMap() as $rootEntity => $meta) {
            foreach ($meta['relations'] as $name => $entity) {
                if (self::INDEX_ENTITY_FIRST_CHILD == $val) {
                    return $entity;
                }

                if ($entity == $innerEntity) {
                    $return = [
                        self::INDEX_ENTITY_PARENT      => $rootEntity,
                        self::INDEX_FK_RELATION_NAME   => $name,
                    ][$val];

                    return $return;
                }
            }
        }

        throw new Exceptions\UnespectedValueException(var_export([
            'val'         => self::$indeToDescriptionMap[$val],
            'innerEntity' => $innerEntity,
            'map'         => $this->getMap(),
        ], true));
    }

    public function numberOfRelationsToEntity(string $entityToReach)
    {
        return $this->mapTargetRelations(
            $this->incrementSubject,
            $subject = 0,
            $entityToReach
        );
    }

    public function listOfParentsOf(string $entityToReach)
    {
        return $this->mapTargetRelations(
            $this->appendRootEntityToSubject,
            $subject = [],
            $entityToReach
        );
    }

    public function getEntitiesPath()
    {
        if (!$this->entitiesPath) {
            throw new Exceptions\UndefinedPathException(
                'Any path was requested'
            );
        }

        return $this->entitiesPath;
    }

    public function removeStep($parentToSkip)
    {
        $this->wrongPath[] = $parentToSkip;
    }

    public function getHashKeyForDestination(string $destination)
    {
        return md5($this->entity . $destination);
    }

    private function getMap()
    {
        if (!$this->map) {
            $this->map = $this->mapper->getMap();
        }

        return $this->map;
    }

    public function addEntity(array $parents, $rootEntity) : array
    {
        $parents[] = $rootEntity;

        return $parents;
    }

    public function mapTargetRelations(
        callable $action,
        $subject,
        string $entityToReach
    ) {
        foreach ($this->getMap() as $rootEntity => $meta) {
            foreach ($meta['relations'] as $name => $relationEntity) {
                if ($relationEntity == $entityToReach) {
                    $subject = $action($subject, $rootEntity);
                }
            }
        }

        return $subject;
    }
}
