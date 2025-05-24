<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524132922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE card_stat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, card_value VARCHAR(10) NOT NULL, count INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, player_id INTEGER NOT NULL, num_hands INTEGER NOT NULL, bet_per_hand DOUBLE PRECISION NOT NULL, outcome VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_4586AAFB99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4586AAFB99E6F5DF ON game_session (player_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, balance DOUBLE PRECISION NOT NULL)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE card_stat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_session
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player
        SQL);
    }
}
