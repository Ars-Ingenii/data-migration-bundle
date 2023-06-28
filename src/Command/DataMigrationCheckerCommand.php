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

namespace DataMigrationBundle\Command;

use DataMigrationBundle\Entity\DataMigrationInterface;
use DataMigrationBundle\Factory\DataMigrationFactory;
use DataMigrationBundle\Repository\DataMigrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataMigrationCheckerCommand extends Command
{
    protected static $defaultName = 'ars:migrate:data:check';
    private EntityManagerInterface $entityManager;

    private DataMigrationRepository $dataMigrationRepository;

    /** @var DataMigrationInterface[] */
    private iterable $dataMigrations;
    public function __construct(
        EntityManagerInterface $entityManager,
        DataMigrationRepository $dataMigrationRepository,
        iterable $dataMigrations
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->dataMigrationRepository = $dataMigrationRepository;
        $this->dataMigrations = $dataMigrations;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Searches for new migrations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->dataMigrations as $dataMigration) {
            if ($dataMigration instanceof DataMigrationInterface) {
                $label = $dataMigration->getLabel();
                $record = $this->dataMigrationRepository->findOneBy(['label' => $label]);

                if ($this->validate($label) === false) {
                    $output->writeln('Migration with label ' . $label . ' has been skipped. Bad naming.');
                    continue;
                }

                if ($record === null) {
                    $newDataMigrationData = DataMigrationFactory::create($dataMigration->getLabel(), get_class($dataMigration));
                    $this->entityManager->persist($newDataMigrationData);
                }
            }
        }

        $this->entityManager->flush();
        return 1;
    }

    private function validate(string $label): bool
    {
        return (bool) preg_match('/^ARS-\d{3,5}$/', $label);
    }
}
