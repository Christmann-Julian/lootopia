<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329171114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, icon VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge_translation (id INT AUTO_INCREMENT NOT NULL, badge_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_5A9077B8F7A2C2FC (badge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, icon VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_translation (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_3F2070412469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4FBF094FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hunt (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, category_id INT DEFAULT NULL, rarity_id INT NOT NULL, lat DOUBLE PRECISION NOT NULL, lon DOUBLE PRECISION NOT NULL, INDEX IDX_21FA5947979B1AD6 (company_id), INDEX IDX_21FA594712469DE2 (category_id), INDEX IDX_21FA5947F3747573 (rarity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hunt_translation (id INT AUTO_INCREMENT NOT NULL, hunt_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, question VARCHAR(255) NOT NULL, answer VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_3F38C0FF2585A34B (hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rank (id INT AUTO_INCREMENT NOT NULL, experience_min INT NOT NULL, experience_max INT NOT NULL, level INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rank_translation (id INT AUTO_INCREMENT NOT NULL, rank_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_70C5CD8E7616678F (rank_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rarity (id INT AUTO_INCREMENT NOT NULL, min_experience INT NOT NULL, experience_gain INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rarity_translation (id INT AUTO_INCREMENT NOT NULL, rarity_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_6CCD13F1F3747573 (rarity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward (id INT AUTO_INCREMENT NOT NULL, hunt_id INT NOT NULL, code VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, end_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_4ED172532585A34B (hunt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward_translation (id INT AUTO_INCREMENT NOT NULL, reward_id INT NOT NULL, title VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_A1FCA8BDE466ACA1 (reward_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_badge (user_id INT NOT NULL, badge_id INT NOT NULL, INDEX IDX_1C32B345A76ED395 (user_id), INDEX IDX_1C32B345F7A2C2FC (badge_id), PRIMARY KEY(user_id, badge_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reward (user_id INT NOT NULL, reward_id INT NOT NULL, INDEX IDX_2B83696EA76ED395 (user_id), INDEX IDX_2B83696EE466ACA1 (reward_id), PRIMARY KEY(user_id, reward_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge_translation ADD CONSTRAINT FK_5A9077B8F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE category_translation ADD CONSTRAINT FK_3F2070412469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hunt ADD CONSTRAINT FK_21FA5947979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE hunt ADD CONSTRAINT FK_21FA594712469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE hunt ADD CONSTRAINT FK_21FA5947F3747573 FOREIGN KEY (rarity_id) REFERENCES rarity (id)');
        $this->addSql('ALTER TABLE hunt_translation ADD CONSTRAINT FK_3F38C0FF2585A34B FOREIGN KEY (hunt_id) REFERENCES hunt (id)');
        $this->addSql('ALTER TABLE rank_translation ADD CONSTRAINT FK_70C5CD8E7616678F FOREIGN KEY (rank_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE rarity_translation ADD CONSTRAINT FK_6CCD13F1F3747573 FOREIGN KEY (rarity_id) REFERENCES rarity (id)');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED172532585A34B FOREIGN KEY (hunt_id) REFERENCES hunt (id)');
        $this->addSql('ALTER TABLE reward_translation ADD CONSTRAINT FK_A1FCA8BDE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD rank_id INT DEFAULT NULL, ADD pseudo VARCHAR(255) NOT NULL, ADD experience INT NOT NULL, ADD hunt_count INT NOT NULL, ADD reward_count INT NOT NULL, DROP company');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497616678F FOREIGN KEY (rank_id) REFERENCES rank (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64986CC499D ON user (pseudo)');
        $this->addSql('CREATE INDEX IDX_8D93D6497616678F ON user (rank_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497616678F');
        $this->addSql('ALTER TABLE badge_translation DROP FOREIGN KEY FK_5A9077B8F7A2C2FC');
        $this->addSql('ALTER TABLE category_translation DROP FOREIGN KEY FK_3F2070412469DE2');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FA76ED395');
        $this->addSql('ALTER TABLE hunt DROP FOREIGN KEY FK_21FA5947979B1AD6');
        $this->addSql('ALTER TABLE hunt DROP FOREIGN KEY FK_21FA594712469DE2');
        $this->addSql('ALTER TABLE hunt DROP FOREIGN KEY FK_21FA5947F3747573');
        $this->addSql('ALTER TABLE hunt_translation DROP FOREIGN KEY FK_3F38C0FF2585A34B');
        $this->addSql('ALTER TABLE rank_translation DROP FOREIGN KEY FK_70C5CD8E7616678F');
        $this->addSql('ALTER TABLE rarity_translation DROP FOREIGN KEY FK_6CCD13F1F3747573');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED172532585A34B');
        $this->addSql('ALTER TABLE reward_translation DROP FOREIGN KEY FK_A1FCA8BDE466ACA1');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345A76ED395');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345F7A2C2FC');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696EA76ED395');
        $this->addSql('ALTER TABLE user_reward DROP FOREIGN KEY FK_2B83696EE466ACA1');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE badge_translation');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_translation');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE hunt');
        $this->addSql('DROP TABLE hunt_translation');
        $this->addSql('DROP TABLE rank');
        $this->addSql('DROP TABLE rank_translation');
        $this->addSql('DROP TABLE rarity');
        $this->addSql('DROP TABLE rarity_translation');
        $this->addSql('DROP TABLE reward');
        $this->addSql('DROP TABLE reward_translation');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('DROP TABLE user_reward');
        $this->addSql('DROP INDEX UNIQ_8D93D64986CC499D ON user');
        $this->addSql('DROP INDEX IDX_8D93D6497616678F ON user');
        $this->addSql('ALTER TABLE user ADD company VARCHAR(255) DEFAULT NULL, DROP rank_id, DROP pseudo, DROP experience, DROP hunt_count, DROP reward_count');
    }
}
