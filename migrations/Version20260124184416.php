<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124184416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, capacity INT DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, public_identifier VARCHAR(26) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, mobile_phone VARCHAR(20) DEFAULT NULL, fixed_phone VARCHAR(20) DEFAULT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_88BDF5E9E7927C74 (email), UNIQUE INDEX UNIQ_88BDF5E9A1CE58F6 (public_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_57698A6A77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE log_authentication (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, event_type VARCHAR(32) NOT NULL, occurred_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, INDEX IDX_3BB1C59EA76ED395 (user_id), INDEX idx_log_authentication_occurred_at (occurred_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE log_reset_password (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, event_type VARCHAR(32) NOT NULL, occurred_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, INDEX IDX_5A0E9B2EA76ED395 (user_id), INDEX idx_log_reset_password_occurred_at (occurred_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE log_authentication ADD CONSTRAINT FK_3BB1C59EA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('CREATE TABLE cron_task_run (id INT AUTO_INCREMENT NOT NULL, command VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, duration_ms INT DEFAULT NULL, exit_code INT DEFAULT NULL, summary VARCHAR(255) DEFAULT NULL, output LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, context JSON DEFAULT NULL, INDEX idx_cron_task_run_started_at (started_at), INDEX idx_cron_task_run_status (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log_reset_password ADD CONSTRAINT FK_5A0E9B2EA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE log_authentication');
        $this->addSql('DROP TABLE cron_task_run');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE log_reset_password');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
