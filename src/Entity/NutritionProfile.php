<?php

namespace App\Entity;

use App\Repository\NutritionProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NutritionProfileRepository::class)]
class NutritionProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $dailyCalorieGoal = 2000;

    #[ORM\Column(length: 50)]
    private ?string $activityLevel = 'moderate';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $dietaryGoal = null;

    #[ORM\Column(nullable: true)]
    private ?int $targetProtein = null;

    #[ORM\Column(nullable: true)]
    private ?int $targetCarbs = null;

    #[ORM\Column(nullable: true)]
    private ?int $targetFat = null;

    #[ORM\OneToOne(inversedBy: 'nutritionProfile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDailyCalorieGoal(): ?int
    {
        return $this->dailyCalorieGoal;
    }

    public function setDailyCalorieGoal(int $dailyCalorieGoal): self
    {
        $this->dailyCalorieGoal = $dailyCalorieGoal;
        return $this;
    }

    public function getActivityLevel(): ?string
    {
        return $this->activityLevel;
    }

    public function setActivityLevel(string $activityLevel): self
    {
        $this->activityLevel = $activityLevel;
        return $this;
    }

    public function getDietaryGoal(): ?string
    {
        return $this->dietaryGoal;
    }

    public function setDietaryGoal(?string $dietaryGoal): self
    {
        $this->dietaryGoal = $dietaryGoal;
        return $this;
    }

    public function getTargetProtein(): ?int
    {
        return $this->targetProtein;
    }

    public function setTargetProtein(?int $targetProtein): self
    {
        $this->targetProtein = $targetProtein;
        return $this;
    }

    public function getTargetCarbs(): ?int
    {
        return $this->targetCarbs;
    }

    public function setTargetCarbs(?int $targetCarbs): self
    {
        $this->targetCarbs = $targetCarbs;
        return $this;
    }

    public function getTargetFat(): ?int
    {
        return $this->targetFat;
    }

    public function setTargetFat(?int $targetFat): self
    {
        $this->targetFat = $targetFat;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Helper method to calculate daily calorie goal based on activity level
    public function calculateDailyCalorieGoal(int $baseCalories = 2000): int
    {
        $multipliers = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9,
        ];

        $multiplier = $multipliers[$this->activityLevel] ?? 1.55;
        return (int) ($baseCalories * $multiplier);
    }
}
