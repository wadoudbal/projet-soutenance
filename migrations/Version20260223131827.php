<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223131827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `set` (id INT AUTO_INCREMENT NOT NULL, reps INT NOT NULL, weight DOUBLE PRECISION NOT NULL, workout_id INT DEFAULT NULL, INDEX IDX_E61425DCA6CCCFC9 (workout_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `set` ADD CONSTRAINT FK_E61425DCA6CCCFC9 FOREIGN KEY (workout_id) REFERENCES workout (id)');
        $this->addSql('ALTER TABLE workout ADD serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT FK_649FFB72E934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id)');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT FK_649FFB72A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `set` DROP FOREIGN KEY FK_E61425DCA6CCCFC9');
        $this->addSql('DROP TABLE `set`');
        $this->addSql('ALTER TABLE workout DROP FOREIGN KEY FK_649FFB72E934951A');
        $this->addSql('ALTER TABLE workout DROP FOREIGN KEY FK_649FFB72A76ED395');
        $this->addSql('ALTER TABLE workout DROP serie');
    }
}
