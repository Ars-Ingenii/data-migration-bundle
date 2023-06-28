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
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataMigrationCheckerCommand extends Command
{
    protected static $defaultName = 'ars:migrate:data:check';
    private EntityManagerInterface $entityManager;

    private DataMigrationRepository $dataMigrationRepository;

    private LoggerInterface $logger;

    /** @var DataMigrationInterface[] */
    private iterable $dataMigrations;
    public function __construct(
        EntityManagerInterface $entityManager,
        DataMigrationRepository $dataMigrationRepository,
        LoggerInterface $logger,
        iterable $dataMigrations
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->dataMigrationRepository = $dataMigrationRepository;
        $this->dataMigrations = $dataMigrations;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Searches for new migrations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
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
        } catch (\Exception $exception) {
            $this->logger->error(
                'Data migration checker error occurred.',
                [
                    'message' => $exception->getMessage()
                ]
            );
            $output->writeln('Exception occurred. ' . $exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validate(string $label): bool
    {
        return (bool) preg_match('/^ARS-\d{3,5}$/', $label);
    }
}
