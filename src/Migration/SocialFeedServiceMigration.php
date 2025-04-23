<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    social-feed-bundle
 * @link       https://github.com/pdir/social-feed-bundle
 * @license    http://www.gnu.org/licences/lgpl-3.0.html LGPL
 * @author     Mathias Arzberger <develop@pdir.de>
 * @author     Philipp Seibt <develop@pdir.de>
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\SocialFeedBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class SocialFeedServiceMigration extends AbstractMigration
{
    public function __construct(private Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        // If the database table itself does not exist we should do nothing
        if (!$schemaManager->tablesExist(['tl_social_feed'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_social_feed');

        return
            isset($columns['firstname']) &&
            isset($columns['lastname']) &&
            !isset($columns['name']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery("
            ALTER TABLE
                tl_customers
            ADD
                name varchar(255) NOT NULL DEFAULT ''
        ");

        $stmt = $this->connection->prepare("
            UPDATE
                tl_customers
            SET
                name = CONCAT(firstName, ' ', lastName)
        ");

        $stmt->execute();

        return $this->createResult(
            true,
            'Combined '. $stmt->rowCount().' customer names.'
        );
    }
}
