<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reorder columns: move trabajador_id after cliente_id';
    }

    public function up(Schema $schema): void
    {
        // Modify column position - move trabajador_id after cliente_id
        $this->addSql('ALTER TABLE pedidos MODIFY trabajador_id INT DEFAULT NULL AFTER cliente_id');
    }

    public function down(Schema $schema): void
    {
        // Revert the change
        $this->addSql('ALTER TABLE pedidos MODIFY trabajador_id INT DEFAULT NULL AFTER pagado');
    }
}
