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

use DataMigrationBundle\Entity\DataMigration;
use DataMigrationBundle\Repository\DataMigrationRepository;
use Doctrine\ORM\EntityManagerInterface;

class DataMigrationChecker
{
    private EntityManagerInterface $entityManager;

    /** @var DataMigrationInterface[] */
    private iterable $dataMigrations;

    private DataMigrationRepository $dataMigrationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $dataMigrations
    ) {
        $this->entityManager = $entityManager;
        $this->dataMigrations = $dataMigrations;
        $this->dataMigrationRepository = $entityManager->getRepository(DataMigration::class);
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
