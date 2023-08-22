<?php

/*
 * ------------------------------------------------------------
 * Copyright (c) Ars Ingenii, UAB. All rights reserved.
 *
 * This Software is the property of Ars Ingenii, UAB
 * and is protected by copyright law – it is NOT Freeware.
 *
 * The complete license agreement can be found here:
 * http://www.arsingenii.lt/license/mpl/
 * ------------------------------------------------------------
 */

namespace DataMigrationBundle\Repository;

use DataMigrationBundle\Entity\DataMigration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

class DataMigrationRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, new ClassMetadata(DataMigration::class));
    }

    public function getLabels(): array
    {
        $results = $this->createQueryBuilder('dm')
            ->select('dm.label')
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'label');
    }
}
