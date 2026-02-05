<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class DatabaseWebTestCase extends WebTestCase
{
    protected function setUpDatabase(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata === []) {
            return;
        }

        $connection = $entityManager->getConnection();
        $schemaTool = new SchemaTool($entityManager);
        $platform = $connection->getDatabasePlatform();

        // Run schema updates outside any transaction so DDL commits immediately
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
        $connection->setAutoCommit(true);

        $isMysql = $platform instanceof AbstractMySQLPlatform;
        if ($isMysql) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        }

        try {
            // Drop tables that exist in DB (in case of leftover from previous runs)
            $schemaManager = $connection->createSchemaManager();
            $existingTables = $schemaManager->listTableNames();
            // Also drop tables we're about to create (from metadata) for full coverage
            $schema = $schemaTool->getSchemaFromMetadata($metadata);
            $schemaName = $schema->getName();
            $metadataTableNames = array_map(
                static fn($table) => $table->getShortestName($schemaName),
                $schema->getTables()
            );
            $allTablesToDrop = array_unique(array_merge($existingTables, $metadataTableNames));

            foreach ($allTablesToDrop as $tableName) {
                $quoted = $platform->quoteIdentifier($tableName);
                try {
                    $connection->executeStatement('DROP TABLE IF EXISTS ' . $quoted);
                } catch (\Throwable) {
                    // Ignore errors (e.g. table already dropped)
                }
            }

            $params = $connection->getParams();
            $dbname = $params['dbname'] ?? $params['path'] ?? null;
            if ($dbname !== null && $isMysql) {
                $connection->executeStatement('USE ' . $platform->quoteIdentifier($dbname));
            }

            $schemaTool->createSchema($metadata);
            $entityManager->clear();
        } finally {
            if ($isMysql) {
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
            }
        }
    }
}
