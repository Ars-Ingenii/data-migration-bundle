# Data Migration Bundle

Bundle is used to run console commands that can run data migrations.

## Installation

### Step 1: Install DataMigration bundle

DataMigration bundle is installed using [Composer](https://getcomposer.org).

```bash
# You can require any version you need, check the latest stable to make sure you are using the newest version.
$ composer require ars-ingenii/data-migration-bundle
```

### Step 2: Enable FilterManager bundle

Enable Filter Manager bundle in your AppKernel:

```php
// config/bundles.php

<?php

return [
    ...

    DataMigrationBundle\DataMigrationBundle::class => ['all' => true],

    ...
];
```

### Step 3: Add import to configuration

Add minimal configuration for Elasticsearch and FilterManager bundles.

```yaml
# app/config/services/data_migration.yaml

imports:
  - { resource: "@DataMigrationBundle/Resources/config/config.yaml" }

```
### Step 4: Update you database

```bash
$ bin/console doctrine:migrations:diff
$ bin/console doctrine:migrations:migrate
```

This will add new table to your database 'ars_data_migration'

## Documentation

### Commands

```bash
# Searches for new migrations and saves them in the database.
$ bin/console ars:migrate:data:check
```

```bash
# Runs all migrations that were not yet executed.
$ bin/console ars:migrate:data --all
```

```bash
# Runs migration by name, doesnt matter if it was executed or not. 
$ bin/console ars:migrate:data {name}
```

### Creating data migration class

Class needs to implement `DataMigrationBundle\Entity\DataMigrationInterface`

Interface comes with two methods:

```php
interface DataMigrationInterface
{
    // `migrate` method used to migrate new data changes to your database.
    public function migrate(): void;
    
    // `getLabel` method is used to return data migrations name. Migration name should match regular expression ^ARS-\d{3,5}$ 
    public function getLabel(): string;
}
```

### Class example

```php
<?php
...

use App\Entity\Product\Product;
use App\Repository\ProductRepository;
use DataMigrationBundle\Entity\DataMigrationInterface;
use Doctrine\ORM\EntityManagerInterface;

class ARS2153 implements DataMigrationInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function migrate(): void
    {
        /** @var Product[] $products */
        $products = $this->productRepository->findAll();

        /** @var Product $product */
        foreach ($products as $product) {
            $product->setDeposit(true);
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    public function getLabel(): string
    {
        return 'ARS-2153';
    }
}

```

### Migration configuration

Migration should be configured. It should have a tag `data_migration` and it should be `public: true`. Example:

```yaml
services:
  ARS-2153:
    class: App\Resources\DataMigration\ARS2153
    public: true
    arguments:
      - '@doctrine.orm.entity_manager'
      - '@sylius.repository.product'
    tags:
      - { name: data_migration }
```
