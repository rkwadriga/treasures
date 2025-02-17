<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217131948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, owned_by_id INT NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', token VARCHAR(68) NOT NULL, scopes JSON NOT NULL, INDEX IDX_7BA2F5EB5E70BCD7 (owned_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB5E70BCD7 FOREIGN KEY (owned_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dragon_treasure RENAME INDEX idx_dragon_treasure_owner_id TO IDX_9E31BF5F7E3C61F9');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_user_username TO UNIQ_8D93D649F85E0677');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EB5E70BCD7');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('ALTER TABLE dragon_treasure RENAME INDEX idx_9e31bf5f7e3c61f9 TO IDX_DRAGON_TREASURE_OWNER_ID');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649f85e0677 TO UNIQ_USER_USERNAME');
    }
}
