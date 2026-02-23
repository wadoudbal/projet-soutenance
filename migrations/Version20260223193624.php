<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223193624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `set` ADD CONSTRAINT FK_E61425DCA6CCCFC9 FOREIGN KEY (workout_id) REFERENCES workout (id)');
        $this->addSql('ALTER TABLE user ADD poids DOUBLE PRECISION DEFAULT NULL, ADD taille INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT FK_649FFB72E934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id)');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT FK_649FFB72A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `set` DROP FOREIGN KEY FK_E61425DCA6CCCFC9');
        $this->addSql('ALTER TABLE user DROP poids, DROP taille');
        $this->addSql('ALTER TABLE workout DROP FOREIGN KEY FK_649FFB72E934951A');
        $this->addSql('ALTER TABLE workout DROP FOREIGN KEY FK_649FFB72A76ED395');
    }
}
