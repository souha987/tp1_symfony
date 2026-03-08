<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301180637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD titre VARCHAR(255) NOT NULL, ADD contenu LONGTEXT NOT NULL, ADD auteur VARCHAR(100) NOT NULL, ADD date_creation DATETIME NOT NULL, ADD publie TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP titre, DROP contenu, DROP auteur, DROP date_creation, DROP publie');
    }
}
