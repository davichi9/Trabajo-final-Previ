<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504154500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema with Prendas, Clientes, Trabajadores, and Pedidos tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prendas (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, precio DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clientes (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, telefono_numero VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, domicilio VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trabajadores (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, telefono_numero VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, contraseña VARCHAR(255) NOT NULL, rol VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pedidos (id INT AUTO_INCREMENT NOT NULL, cliente_id INT NOT NULL, estado VARCHAR(255) NOT NULL, contenido LONGTEXT NOT NULL, fecha_entrada DATETIME NOT NULL, fecha_salida DATETIME DEFAULT NULL, precio DOUBLE PRECISION NOT NULL, pagado TINYINT(1) NOT NULL, PRIMARY KEY(id), KEY IDX_CD96D4D06F446D0F (cliente_id), CONSTRAINT FK_CD96D4D06F446D0F FOREIGN KEY (cliente_id) REFERENCES clientes (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS pedidos');
        $this->addSql('DROP TABLE IF EXISTS trabajadores');
        $this->addSql('DROP TABLE IF EXISTS clientes');
        $this->addSql('DROP TABLE IF EXISTS prendas');
    }
}
