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

declare(strict_types=1);

namespace DataMigrationBundle\Service;

use DataMigrationBundle\Repository\DataMigrationRepository;
use Doctrine\ORM\EntityManagerInterface;

class DataMigrationChecker
{
    private EntityManagerInterface $entityManager;

    /** @var DataMigrationInterface[] */
    private iterable $dataMigrations;

    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $dataMigrations
    ) {
        $this->entityManager = $entityManager;
        $this->dataMigrations = $dataMigrations;
    }

    public function findNewDataMigrations()
    {

    }
}
