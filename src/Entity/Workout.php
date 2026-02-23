<?php

namespace App\Entity;

use App\Repository\WorkoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkoutRepository::class)]
class Workout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $weight = null;

    #[ORM\Column]
    private ?int $reps = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'workouts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Exercise $exercise = null;

    #[ORM\ManyToOne(inversedBy: 'workouts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Set>
     */
    #[ORM\OneToMany(targetEntity: Set::class, mappedBy: 'workout')]
    private Collection $sets;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serie = null;

    public function __construct()
    {
        $this->sets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getReps(): ?int
    {
        return $this->reps;
    }

    public function setReps(int $reps): static
    {
        $this->reps = $reps;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExercise(): ?Exercise
    {
        return $this->exercise;
    }

    public function setExercise(?Exercise $exercise): static
    {
        $this->exercise = $exercise;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Set>
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    public function addSet(Set $set): static
    {
        if (!$this->sets->contains($set)) {
            $this->sets->add($set);
            $set->setWorkout($this);
        }

        return $this;
    }

    public function removeSet(Set $set): static
    {
        if ($this->sets->removeElement($set)) {
            // set the owning side to null (unless already changed)
            if ($set->getWorkout() === $this) {
                $set->setWorkout(null);
            }
        }

        return $this;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function setSerie(?string $serie): static
    {
        $this->serie = $serie;

        return $this;
    }
}
