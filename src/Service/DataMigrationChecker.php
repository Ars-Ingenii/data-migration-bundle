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

    private DataMigrationRepository $dataMigrationRepository;

    /** @var DataMigrationInterface[] */
    private iterable $dataMigrations;

    public function __construct(
        EntityManagerInterface $entityManager,
        DataMigrationRepository $dataMigrationRepository,
        iterable $dataMigrations
    ) {
        $this->entityManager = $entityManager;
        $this->dataMigrations = $dataMigrations;
        $this->dataMigrationRepository = $dataMigrationRepository;
    }

    public function findNewDataMigrations()
    {
        foreach ($this->dataMigrations as $dataMigration) {
            if ($dataMigration instanceof DataMigrationInterface) {
                $label = $dataMigration->getLabel();
                $dataMigration = $this->dataMigrationRepository->findOneBy(['label' => $label]);
            }
        }
    }
}
