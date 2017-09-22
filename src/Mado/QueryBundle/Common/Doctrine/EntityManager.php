<?php

namespace Mado\QueryBundle\Common\Doctrine;

use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\Tools\Setup;

final class EntityManager
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = DoctrineEntityManager::create([
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . '/../../../../../db.sqlite',
            ], Setup::createAnnotationMetadataConfiguration(
                array(__DIR__ . "/../../../../../src"),
                $isDevMode = true
            ));
        }

        return self::$instance;
    }
}
