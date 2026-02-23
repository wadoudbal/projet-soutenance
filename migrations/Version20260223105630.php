<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223105630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exercise (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, muscle_group VARCHAR(100) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE workout (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, weight DOUBLE PRECISION NOT NULL, reps INT NOT NULL, created_at DATETIME NOT NULL, exercise_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_649FFB72E934951A (exercise_id), INDEX IDX_649FFB72A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT FK_649FFB72E934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id)');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT FK_649FFB72A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workout DROP FOREIGN KEY FK_649FFB72E934951A');
        $this->addSql('ALTER TABLE workout DROP FOREIGN KEY FK_649FFB72A76ED395');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE workout');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
