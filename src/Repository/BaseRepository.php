<?php

namespace App\Repository;

use App\Entity\Identifiable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class BaseRepository
 * @package App\Repository
 */
abstract class BaseRepository extends ServiceEntityRepository
{
    /**
     * Returns an array of the given entities with entity ID as an index.
     *
     * @param Identifiable[] $entities
     *
     * @return Identifiable[]
     */
    protected function addIndexes(array $entities): array
    {
        $indexedEntities = [];

        foreach ($entities as $entity) {
            if (!$entity instanceof Identifiable) {
                continue;
            }
            $indexedEntities[$entity->getId()] = $entity;
        }

        return $indexedEntities;
    }
}
