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
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, public_identifier VARCHAR(26) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, mobile_phone VARCHAR(20) DEFAULT NULL, fixed_phone VARCHAR(20) DEFAULT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_88BDF3E9E7927C74 (email), UNIQUE INDEX UNIQ_88BDF3E9EDBC1707 (public_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_57698A6A77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE log_authentication (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, event_type VARCHAR(32) NOT NULL, occurred_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, INDEX IDX_18E8F8ABA76ED395 (user_id), INDEX idx_log_authentication_occurred_at (occurred_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE log_reset_password (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, event_type VARCHAR(32) NOT NULL, occurred_at DATETIME NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, INDEX IDX_5FC40DB3A76ED395 (user_id), INDEX idx_log_reset_password_occurred_at (occurred_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cron_task_run (id INT AUTO_INCREMENT NOT NULL, command VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, duration_ms INT DEFAULT NULL, exit_code INT DEFAULT NULL, summary VARCHAR(255) DEFAULT NULL, output LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, context JSON DEFAULT NULL, INDEX idx_cron_task_run_started_at (started_at), INDEX idx_cron_task_run_status (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE backoffice_menu_config (id INT AUTO_INCREMENT NOT NULL, config JSON NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE additional_fee_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5F19E70777153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, category VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_B65A862F77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_93151B8277153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE organization (id INT AUTO_INCREMENT NOT NULL, siret VARCHAR(20) DEFAULT NULL, legal_name VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, legal_nature VARCHAR(255) DEFAULT NULL, organization_type VARCHAR(50) DEFAULT NULL, billing_same_as_head_office TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, head_office_line1 VARCHAR(255) DEFAULT NULL, head_office_line2 VARCHAR(255) DEFAULT NULL, head_office_line3 VARCHAR(255) DEFAULT NULL, head_office_postal_code VARCHAR(10) DEFAULT NULL, head_office_city VARCHAR(100) DEFAULT NULL, head_office_country VARCHAR(2) DEFAULT NULL, head_office_source VARCHAR(50) DEFAULT NULL, head_office_external_id VARCHAR(255) DEFAULT NULL, head_office_latitude DOUBLE PRECISION DEFAULT NULL, head_office_longitude DOUBLE PRECISION DEFAULT NULL, billing_line1 VARCHAR(255) DEFAULT NULL, billing_line2 VARCHAR(255) DEFAULT NULL, billing_line3 VARCHAR(255) DEFAULT NULL, billing_postal_code VARCHAR(10) DEFAULT NULL, billing_city VARCHAR(100) DEFAULT NULL, billing_country VARCHAR(2) DEFAULT NULL, billing_source VARCHAR(50) DEFAULT NULL, billing_external_id VARCHAR(255) DEFAULT NULL, billing_latitude DOUBLE PRECISION DEFAULT NULL, billing_longitude DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_C1EE637C26E94372 (siret), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE organization_contact (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(50) NOT NULL, title VARCHAR(20) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, organization_id INT NOT NULL, INDEX IDX_81B06E7B32C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, status VARCHAR(50) NOT NULL, ticketing_type VARCHAR(50) NOT NULL, security_deposit NUMERIC(10, 2) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, room_id INT NOT NULL, organization_id INT NOT NULL, organization_contact_id INT DEFAULT NULL, event_type_id INT DEFAULT NULL, INDEX IDX_42C8495554177093 (room_id), INDEX IDX_42C8495532C8A3DE (organization_id), INDEX IDX_42C84955EFAB7A5 (organization_contact_id), INDEX IDX_42C84955401B253C (event_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation_additional_fee (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(10, 2) NOT NULL, label VARCHAR(255) DEFAULT NULL, reservation_id INT NOT NULL, additional_fee_type_id INT NOT NULL, INDEX IDX_A3F66AF1B83297E7 (reservation_id), INDEX IDX_A3F66AF1165A1E01 (additional_fee_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, surface_area NUMERIC(10, 2) DEFAULT NULL, seated_capacity INT DEFAULT NULL, standing_capacity INT DEFAULT NULL, is_pmr_accessible TINYINT DEFAULT 0 NOT NULL, has_elevator TINYINT DEFAULT 0 NOT NULL, has_pmr_restrooms TINYINT DEFAULT 0 NOT NULL, has_emergency_exits TINYINT DEFAULT 0 NOT NULL, is_erp_compliant TINYINT DEFAULT 0 NOT NULL, erp_type VARCHAR(50) DEFAULT NULL, erp_category VARCHAR(50) DEFAULT NULL, security_staff_required TINYINT DEFAULT 0 NOT NULL, opening_hours_schema LONGTEXT DEFAULT NULL, min_rental_duration_minutes INT DEFAULT NULL, max_rental_duration_minutes INT DEFAULT NULL, booking_lead_time_days INT DEFAULT NULL, catering_allowed TINYINT DEFAULT 0 NOT NULL, alcohol_allowed TINYINT DEFAULT 0 NOT NULL, alcohol_legal_notice LONGTEXT DEFAULT NULL, music_allowed TINYINT DEFAULT 0 NOT NULL, sacem_required TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, venue_id INT NOT NULL, INDEX IDX_729F519B40A73EBA (venue_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_room_type (room_id INT NOT NULL, room_type_id INT NOT NULL, INDEX IDX_73735D2454177093 (room_id), INDEX IDX_73735D24296E3073 (room_type_id), PRIMARY KEY (room_id, room_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_room_layout (room_id INT NOT NULL, room_layout_id INT NOT NULL, INDEX IDX_6F41031C54177093 (room_id), INDEX IDX_6F41031C108B4A73 (room_layout_id), PRIMARY KEY (room_id, room_layout_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_document (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, room_id INT NOT NULL, INDEX IDX_788F4AB254177093 (room_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_equipment (id INT AUTO_INCREMENT NOT NULL, max_quantity INT DEFAULT NULL, exclusive_to_room TINYINT DEFAULT 0 NOT NULL, is_included TINYINT DEFAULT 1 NOT NULL, room_id INT NOT NULL, equipment_type_id INT NOT NULL, INDEX IDX_4F9135EA54177093 (room_id), INDEX IDX_4F9135EAB337437C (equipment_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_layout (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_7B74AD2377153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_pricing (id INT AUTO_INCREMENT NOT NULL, price_category VARCHAR(50) NOT NULL, hourly_rate NUMERIC(10, 2) DEFAULT NULL, daily_rate NUMERIC(10, 2) DEFAULT NULL, currency VARCHAR(3) DEFAULT "EUR" NOT NULL, room_id INT NOT NULL, INDEX IDX_9D310D354177093 (room_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_service (id INT AUTO_INCREMENT NOT NULL, is_included TINYINT DEFAULT 1 NOT NULL, room_id INT NOT NULL, service_type_id INT NOT NULL, INDEX IDX_DBF263254177093 (room_id), INDEX IDX_DBF2632AC8DE0F (service_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_EFDABD4D77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE room_usage (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, usage_type_id INT NOT NULL, INDEX IDX_9A57FFDB54177093 (room_id), INDEX IDX_9A57FFDB9BC8FA8C (usage_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_429DE3C577153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE usage_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2774453277153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE venue (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, public_transport_access LONGTEXT DEFAULT NULL, parking_type VARCHAR(50) DEFAULT NULL, parking_capacity INT DEFAULT NULL, contact_details LONGTEXT DEFAULT NULL, reference_contact_name VARCHAR(255) DEFAULT NULL, delivery_access LONGTEXT DEFAULT NULL, access_map_url VARCHAR(255) DEFAULT NULL, house_rules LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, address_line1 VARCHAR(255) DEFAULT NULL, address_line2 VARCHAR(255) DEFAULT NULL, address_line3 VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(10) DEFAULT NULL, address_city VARCHAR(100) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, address_source VARCHAR(50) DEFAULT NULL, address_external_id VARCHAR(255) DEFAULT NULL, address_latitude DOUBLE PRECISION DEFAULT NULL, address_longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE venue_document (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, venue_id INT NOT NULL, INDEX IDX_B9B2BD5240A73EBA (venue_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE venue_equipment (id INT AUTO_INCREMENT NOT NULL, max_quantity INT DEFAULT NULL, is_included TINYINT DEFAULT 1 NOT NULL, venue_id INT NOT NULL, equipment_type_id INT NOT NULL, INDEX IDX_EF5AEA6540A73EBA (venue_id), INDEX IDX_EF5AEA65B337437C (equipment_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE log_authentication ADD CONSTRAINT FK_18E8F8ABA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log_reset_password ADD CONSTRAINT FK_5FC40DB3A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE organization_contact ADD CONSTRAINT FK_81B06E7B32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955EFAB7A5 FOREIGN KEY (organization_contact_id) REFERENCES organization_contact (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955401B253C FOREIGN KEY (event_type_id) REFERENCES event_type (id)');
        $this->addSql('ALTER TABLE reservation_additional_fee ADD CONSTRAINT FK_A3F66AF1B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE reservation_additional_fee ADD CONSTRAINT FK_A3F66AF1165A1E01 FOREIGN KEY (additional_fee_type_id) REFERENCES additional_fee_type (id)');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE room_room_type ADD CONSTRAINT FK_73735D2454177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_room_type ADD CONSTRAINT FK_73735D24296E3073 FOREIGN KEY (room_type_id) REFERENCES room_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_room_layout ADD CONSTRAINT FK_6F41031C54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_room_layout ADD CONSTRAINT FK_6F41031C108B4A73 FOREIGN KEY (room_layout_id) REFERENCES room_layout (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_document ADD CONSTRAINT FK_788F4AB254177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_equipment ADD CONSTRAINT FK_4F9135EA54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_equipment ADD CONSTRAINT FK_4F9135EAB337437C FOREIGN KEY (equipment_type_id) REFERENCES equipment_type (id)');
        $this->addSql('ALTER TABLE room_pricing ADD CONSTRAINT FK_9D310D354177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_service ADD CONSTRAINT FK_DBF263254177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_service ADD CONSTRAINT FK_DBF2632AC8DE0F FOREIGN KEY (service_type_id) REFERENCES service_type (id)');
        $this->addSql('ALTER TABLE room_usage ADD CONSTRAINT FK_9A57FFDB54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_usage ADD CONSTRAINT FK_9A57FFDB9BC8FA8C FOREIGN KEY (usage_type_id) REFERENCES usage_type (id)');
        $this->addSql('ALTER TABLE venue_document ADD CONSTRAINT FK_1B5F2B2A40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE venue_equipment ADD CONSTRAINT FK_2D3E7A1340A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE venue_equipment ADD CONSTRAINT FK_2D3E7A13B337437C FOREIGN KEY (equipment_type_id) REFERENCES equipment_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE venue_document');
        $this->addSql('DROP TABLE venue_equipment');
        $this->addSql('DROP TABLE reservation_additional_fee');
        $this->addSql('DROP TABLE room_document');
        $this->addSql('DROP TABLE room_equipment');
        $this->addSql('DROP TABLE room_service');
        $this->addSql('DROP TABLE room_usage');
        $this->addSql('DROP TABLE room_room_layout');
        $this->addSql('DROP TABLE room_room_type');
        $this->addSql('DROP TABLE room_pricing');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE organization_contact');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE additional_fee_type');
        $this->addSql('DROP TABLE equipment_type');
        $this->addSql('DROP TABLE event_type');
        $this->addSql('DROP TABLE room_layout');
        $this->addSql('DROP TABLE room_type');
        $this->addSql('DROP TABLE service_type');
        $this->addSql('DROP TABLE usage_type');
        $this->addSql('DROP TABLE venue');

        $this->addSql('DROP TABLE log_authentication');
        $this->addSql('DROP TABLE cron_task_run');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE log_reset_password');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE backoffice_menu_config');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
