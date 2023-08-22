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

namespace DataMigrationBundle\Factory;

use DataMigrationBundle\Entity\DataMigration;

class DataMigrationFactory
{
    public static function create(string $label, string $path): DataMigration
    {
        return (new DataMigration())
            ->setLabel($label)
            ->setPath($path);
    }
}
