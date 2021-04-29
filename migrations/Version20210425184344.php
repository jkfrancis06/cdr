<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210425184344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE dump_t_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dump_t (id INT NOT NULL, flux_appel VARCHAR(255) NOT NULL, data_type VARCHAR(255) NOT NULL, day_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT NOT NULL, imei VARCHAR(255) DEFAULT NULL, num_a VARCHAR(255) NOT NULL, num_b VARCHAR(255) NOT NULL, cell_id VARCHAR(255) DEFAULT NULL, location_num_a VARCHAR(255) DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, os VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, a_nom VARCHAR(255) DEFAULT NULL, a_piece VARCHAR(255) DEFAULT NULL, a_adresse VARCHAR(255) DEFAULT NULL, b_nom VARCHAR(255) DEFAULT NULL, b_piece VARCHAR(255) DEFAULT NULL, b_adresse VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE dump_t_id_seq CASCADE');
        $this->addSql('DROP TABLE dump_t');
    }
}
