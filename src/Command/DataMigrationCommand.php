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

use DataMigrationBundle\Entity\DataMigration;
use DataMigrationBundle\Entity\DataMigrationInterface;
use DataMigrationBundle\Repository\DataMigrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        DataMigrationRepository $dataMigrationRepository,
        LoggerInterface $logger
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->dataMigrationRepository = $dataMigrationRepository;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migrates data to database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $dataMigrations = $this->dataMigrationRepository->findBy(['executed' => false]);

            if (count($dataMigrations) === 0) {
                $output->writeln('No data migrations to execute.');

                return self::SUCCESS;
            }

            /** @var DataMigration $migration */
            foreach ($dataMigrations as $migration) {
                /** @var DataMigrationInterface $service */
                $service = $this->container->get($migration->getLabel());
                $service->migrate();
            }

        } catch (\Exception $exception) {
            $this->logger->error(
                'Data migration error occurred.',
                [
                    'message' => $exception->getMessage()
                ]
            );
            $output->writeln('Exception occurred. ' . $exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    public function setContainer(?ContainerInterface $container)
    {
        $this->container = $container;
    }
}
