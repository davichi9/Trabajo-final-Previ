<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add trabajador foreign key to pedidos table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pedidos ADD trabajador_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pedidos ADD CONSTRAINT FK_2F1C8E2F403220FC FOREIGN KEY (trabajador_id) REFERENCES trabajadores (id)');
        $this->addSql('CREATE INDEX IDX_2F1C8E2F403220FC ON pedidos (trabajador_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pedidos DROP FOREIGN KEY FK_2F1C8E2F403220FC');
        $this->addSql('DROP INDEX IDX_2F1C8E2F403220FC ON pedidos');
        $this->addSql('ALTER TABLE pedidos DROP COLUMN trabajador_id');
    }
}
