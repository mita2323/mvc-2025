<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527214443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__player AS SELECT id, name, balance, num_hands FROM player
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, balance DOUBLE PRECISION NOT NULL, num_hands INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO player (id, name, balance, num_hands) SELECT id, name, balance, num_hands FROM __temp__player
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__player
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__player AS SELECT id, name, balance, num_hands FROM player
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, balance INTEGER NOT NULL, num_hands INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO player (id, name, balance, num_hands) SELECT id, name, balance, num_hands FROM __temp__player
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__player
        SQL);
    }
}
