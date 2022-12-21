<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221216075617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cperson_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE unwanted_number_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cperson (id INT NOT NULL, c_number VARCHAR(255) NOT NULL, a_nom VARCHAR(255) DEFAULT NULL, c_adress VARCHAR(255) DEFAULT NULL, c_operator VARCHAR(255) NOT NULL, c_file_name VARCHAR(255) NOT NULL, c_pic_name VARCHAR(255) DEFAULT \'icon-default.png\', PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE dump_huri (id SERIAL NOT NULL, num_a VARCHAR(255) NOT NULL, num_b VARCHAR(255) NOT NULL, a_nom TEXT DEFAULT NULL, b_nom TEXT DEFAULT NULL, duration INT NOT NULL, day_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, flux_appel VARCHAR(255) NOT NULL, data_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE dump_t (id SERIAL NOT NULL, flux_appel VARCHAR(255) NOT NULL, data_type VARCHAR(255) NOT NULL, day_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT NOT NULL, imei VARCHAR(255) DEFAULT NULL, num_a VARCHAR(255) NOT NULL, num_b VARCHAR(255) NOT NULL, cell_id VARCHAR(255) DEFAULT NULL, location_num_a VARCHAR(255) DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, os VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, a_nom TEXT DEFAULT NULL, a_piece VARCHAR(255) DEFAULT NULL, a_adresse VARCHAR(255) DEFAULT NULL, b_nom TEXT DEFAULT NULL, b_piece VARCHAR(255) DEFAULT NULL, b_adresse VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE trecord (id SERIAL NOT NULL, data_type VARCHAR(255) NOT NULL, flux_appel VARCHAR(255) NOT NULL, num_a VARCHAR(255) NOT NULL, num_b VARCHAR(255) NOT NULL, a_nom TEXT DEFAULT NULL, b_nom TEXT DEFAULT NULL, duration INT NOT NULL, day_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE unwanted_number (id INT NOT NULL, number VARCHAR(255) NOT NULL, unwanted_rows_count INT DEFAULT 0 NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cperson_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE unwanted_number_id_seq CASCADE');
        $this->addSql('DROP TABLE cperson');
        $this->addSql('DROP TABLE dump_huri');
        $this->addSql('DROP TABLE dump_t');
        $this->addSql('DROP TABLE trecord');
        $this->addSql('DROP TABLE unwanted_number');
    }
}
