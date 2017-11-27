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

    public function __construct(
        RelationDatamapper $mapper
    ) {
        $this->map = $mapper->getMap();
    }

    public function setEntity(string $entity)
    {
        $this->entity = $entity;
    }

    public function getFirstParentOf(string $innerEntity)
    {
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
            foreach ($this->map as $root => $meta) {
                if ($this->entity != $root) {
                    unset($this->map[$root]);
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
        foreach ($this->map as $rootEntity => $meta) {
            if (in_array($rootEntity, $this->wrongPath)) {
                unset($this->map[$rootEntity]);
            }
        }

        return '_embedded.' . $this->getPathTo($entityToReach);
    }

    public function keep($val, $innerEntity)
    {
        foreach ($this->map as $rootEntity => $meta) {
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
    }

    public function numberOfRelationsToEntity(string $entityToReach)
    {
        $numberOfRelationsToEntity = 0;

        foreach ($this->map as $rootEntity => $meta) {
            foreach ($meta['relations'] as $name => $entity) {
                if ($entity == $entityToReach) {
                    $numberOfRelationsToEntity++;
                }
            }
        }

        return $numberOfRelationsToEntity;
    }

    public function listOfParentsOf(string $entityToReach)
    {
        $parents = [];

        foreach ($this->map as $rootEntity => $meta) {
            foreach ($meta['relations'] as $name => $entity) {
                if ($entity == $entityToReach) {
                    $parents[] = $rootEntity;
                }
            }
        }

        return $parents;
    }

    public function getEntitiesPath()
    {
        return $this->entitiesPath;
    }

    public function removeStep($parentToSkip)
    {
        $this->wrongPath[] = $parentToSkip;
    }
}
