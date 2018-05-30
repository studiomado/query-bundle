<?php

namespace Mado\QueryBundle\Queries;

use Doctrine\ORM\EntityManager;
use Mado\QueryBundle\Services\StringParser;

class Join
{
    private const AND_OPERATOR_LOGIC = 'AND';

    private const OR_OPERATOR_LOGIC = 'OR';

    private $parser;

    private $entityName;

    private $entityAlias;

    private $manager;

    private $innerJoin;

    private $leftJoin;

    private $joins;

    private $relationEntityAlias;

    public function __construct(string $entityName, string $entityAlias, EntityManager $manager)
    {
        $this->parser  = new StringParser();
        $this->entityName = $entityName;
        $this->entityAlias = $entityAlias;
        $this->manager = $manager;
        $this->innerJoin = [];
        $this->leftJoin = [];
    }

    /**
     * @param String $relation Nome della relazione semplice (groups.name) o con embedded (_embedded.groups.name)
     * @return $this
     */
    public function join(String $relation, $logicOperator = self::AND_OPERATOR_LOGIC)
    {
        $relation = explode('|', $relation)[0];
        $relations = [$relation];

        if (strstr($relation, '_embedded.')) {
            $embeddedFields = explode('.', $relation);
            $this->parser->camelize($embeddedFields[1]);

            // elimino l'ultimo elemento che dovrebbe essere il nome del campo
            unset($embeddedFields[count($embeddedFields) - 1]);

            // elimino il primo elemento _embedded
            unset($embeddedFields[0]);

            $relations = $embeddedFields;
        }

        $entityName = $this->entityName;
        $entityAlias = $this->entityAlias;

        foreach ($relations as $relation) {
            $relation = $this->parser->camelize($relation);
            $relationEntityAlias = 'table_' . $relation;

            $metadata = $this->manager->getClassMetadata($entityName);

            if ($metadata->hasAssociation($relation)) {
                $association = $metadata->getAssociationMapping($relation);

                $fieldName = $this->parser->camelize($association['fieldName']);

                if ($this->noExistsJoin($relationEntityAlias, $relation)) {
                    if ($logicOperator === self::AND_OPERATOR_LOGIC) {
                        $param = [];
                        $param['field'] = $entityAlias . "." . $fieldName;
                        $param['relation'] = $relationEntityAlias;
                        $this->innerJoin[] = $param;
                    } elseif ($logicOperator === self::OR_OPERATOR_LOGIC) {
                        $param = [];
                        $param['field'] = $entityAlias . "." . $fieldName;
                        $param['relation'] = $relationEntityAlias;
                        $this->leftJoin[] = $param;
                    } else {
                        throw new \Exception('Missing Logic operator');
                    }

                    $this->storeJoin($relationEntityAlias, $relation);
                }

                $entityName = $association['targetEntity'];
                $entityAlias = $relationEntityAlias;
            }

            $this->setRelationEntityAlias($relationEntityAlias);
        }

        return $this;
    }

    private function storeJoin($prevEntityAlias, $currentEntityAlias)
    {
        $needle = $prevEntityAlias . '_' . $currentEntityAlias;
        $this->joins[$needle] = $needle;
    }

    private function noExistsJoin($prevEntityAlias, $currentEntityAlias)
    {
        if (null === $this->joins) {
            $this->joins = [];
        }

        $needle = $prevEntityAlias . '_' . $currentEntityAlias;

        return !in_array($needle, $this->joins);
    }

    public function getInnerJoin() :array
    {
        return $this->innerJoin;
    }

    public function getLeftJoin() :array
    {
        return $this->leftJoin;
    }

    private function setRelationEntityAlias(string $relationEntityAlias)
    {
        $this->relationEntityAlias = $relationEntityAlias;
    }

    public function getRelationEntityAlias()
    {
        return $this->relationEntityAlias;
    }
}