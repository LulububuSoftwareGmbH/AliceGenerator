<?php

namespace Trappar\AliceGenerator\Tests\Util;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\Loader\NativeLoader;
use Trappar\AliceGenerator\FixtureGenerationContext;
use Trappar\AliceGenerator\FixtureGenerator;
use Trappar\AliceGenerator\FixtureGeneratorBuilder;
use Trappar\AliceGenerator\Persister\DoctrinePersister;

class FixtureUtils
{
    public static function buildFixtureGeneratorBuilder(bool $setMetadataDirs): FixtureGeneratorBuilder
    {
        $entitiesPaths = ['Trappar\AliceGenerator\Tests\Fixtures' => __DIR__ . '/../Fixtures'];
        $em = self::buildEntityManager($entitiesPaths);

        $fgBuilder = new FixtureGeneratorBuilder();
        if ($setMetadataDirs) {
            $fgBuilder->addMetadataDirs($entitiesPaths);
        }
        $fgBuilder->setPersister(new DoctrinePersister($em));

        return $fgBuilder;
    }

    public static function buildFixtureGenerator(bool $setMetadataDirs = true): FixtureGenerator
    {
        return self::buildFixtureGeneratorBuilder($setMetadataDirs)->build();
    }

    /**
     * @param string|string[]|null $entitiesDirs
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    public static function buildEntityManager($entitiesDirs): EntityManager
    {
        $config = Setup::createConfiguration(true);

        $driver = new AnnotationDriver(new AnnotationReader(), $entitiesDirs);

        $config->setMetadataDriverImpl($driver);

        $conn = array(
            'driver' => 'pdo_sqlite',
            'path'   => __DIR__ . '/db.sqlite',
        );

        return EntityManager::create($conn, $config);
    }

    /**
     * @return object[]
     */
    public static function getObjectsFromFixtures(array $data): array
    {
        $loader = new NativeLoader();
        return $loader->loadData($data)->getObjects();
    }

    /**
     * @param object|object[] $objects
     * @return array<string,array<string,array<string,mixed>>>
     */
    public static function getFixturesFromObjects($objects, FixtureGenerationContext $context = null): array
    {
        return self::buildFixtureGenerator(false)->generateArray($objects, $context);
    }

    /**
     * @param object|object[] $objects
     * @return object[]
     */
    public static function convertObjectToFixtureAndBack($objects, FixtureGenerationContext $context = null): array
    {
        $fixtures = self::getFixturesFromObjects($objects, $context);
        return self::getObjectsFromFixtures($fixtures);
    }
}