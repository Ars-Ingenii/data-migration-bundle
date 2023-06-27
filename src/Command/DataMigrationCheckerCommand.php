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

use DataMigrationBundle\Service\DataMigrationChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataMigrationCheckerCommand extends Command
{
    protected static $defaultName = 'ars:migrate:data:check';
    private DataMigrationChecker $dataMigrationChecker;

    public function __construct(DataMigrationChecker $dataMigrationChecker)
    {
        parent::__construct();

        $this->dataMigrationChecker = $dataMigrationChecker;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Searches for new migrations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dataMigrationChecker->findNewDataMigrations();

        return 1;
    }
}
