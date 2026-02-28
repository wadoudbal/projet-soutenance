<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "activity_logs")] // J'ai renommé la collection pour plus de clarté
class ActivityLog
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: "string")]
    private string $userEmail;

    #[MongoDB\Field(type: "string")]
    private string $action;

    #[MongoDB\Field(type: "date")]
    private \DateTimeInterface $createdAt;

    public function __construct(string $userEmail, string $action)
    {
        $this->userEmail = $userEmail;
        $this->action = $action;
        $this->createdAt = new \DateTime();
    }

    // Getters
    public function getId(): ?string { return $this->id; }
    public function getUserEmail(): string { return $this->userEmail; }
    public function getAction(): string { return $this->action; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}