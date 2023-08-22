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

use DataMigrationBundle\Factory\DataMigrationFactory;
use DataMigrationBundle\Repository\DataMigrationRepository;
use DataMigrationBundle\Resources\DataMigrationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataMigrationCommand extends Command implements ContainerAwareInterface
{
    protected static $defaultName = 'ars:migrate:data';

    protected ContainerInterface $container;

    private EntityManagerInterface $entityManager;

    private DataMigrationRepository $dataMigrationRepository;

    private LoggerInterface $logger;

    private array $dataMigrations = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        DataMigrationRepository $dataMigrationRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->dataMigrationRepository = $dataMigrationRepository;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migrates data to database')
            ->addOption('all', 'all', InputOption::VALUE_OPTIONAL, 'Execute all migrations.', false)
            ->addArgument('name', InputOption::VALUE_OPTIONAL, 'Execute one migration by name.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $label = $input->getArgument('name')[0] ?? null;
            $executeAll = $input->getOption('all');

            if ($executeAll !== false) {
                $this->executeAllMigrations($output);
            }

            if ($label !== null) {
                $this->executeMigrationByLabel($output, $label);
            }
        } catch (\Exception $exception) {
            $this->logger->error(
                'Data migration error occurred.',
                [
                    'message' => $exception->getMessage(),
                ]
            );
            $output->writeln('Exception occurred. ' . $exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function executeAllMigrations(OutputInterface $output): void
    {
        $executedLabels = $this->dataMigrationRepository->getLabels();
        $filteredLabels = array_diff_key($this->dataMigrations, array_flip($executedLabels));

        if (count($filteredLabels) === 0) {
            $output->writeln('No data migrations to execute.');

            return;
        }

        /** @var DataMigrationInterface $migration */
        foreach ($filteredLabels as $migration) {
            $migration->execute();
            $newDataMigrationData = DataMigrationFactory::create($migration->getName(), get_class($migration));
            $this->entityManager->persist($newDataMigrationData);
        }

        $this->entityManager->flush();
    }

    private function executeMigrationByLabel(OutputInterface $output, string $label): void
    {
        if (!isset($this->dataMigrations[$label])) {
            $output->writeln('No data migration found by label:' . $label);

            return;
        }

        /** @var DataMigrationInterface $service */
        $service = $this->dataMigrations[$label];
        $service->execute();

        $record = $this->dataMigrationRepository->findOneBy(['label' => $label]);

        if ($record === null) {
            $newDataMigrationData = DataMigrationFactory::create($service->getName(), get_class($service));
            $this->entityManager->persist($newDataMigrationData);
            $this->entityManager->flush();
        }
    }

    public function addDataMigration(DataMigrationInterface $definition): void
    {
        $this->dataMigrations[$definition->getName()] = $definition;
    }
}
