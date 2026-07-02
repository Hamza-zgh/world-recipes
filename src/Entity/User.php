<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $fullName = null;

    #[ORM\Column(length: 20)]
    private ?string $role = 'ROLE_USER';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profileImage = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // ========== NEW RELATIONSHIPS ==========

    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'user')]
    private Collection $recipes;

    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'user')]
    private Collection $menus;

    #[ORM\OneToOne(targetEntity: NutritionProfile::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?NutritionProfile $nutritionProfile = null;

    // ========== END NEW RELATIONSHIPS ==========

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->recipes = new ArrayCollection();
        $this->menus = new ArrayCollection();
    }

    // ================= Security =================

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    // ================= Getters & Setters =================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }
    public function getProfileImageUrl(): string
    {
        if ($this->profileImage) {
            return '/uploads/profile/' . $this->profileImage;
        }

        // Default avatar based on first letter of name
        $firstLetter = strtoupper(substr($this->fullName, 0, 1));
        $color = dechex(crc32($this->email) % 0xFFFFFF);
        return "https://ui-avatars.com/api/?name=$firstLetter&background=$color&color=fff&size=150";
    }
    public function setProfileImage(?string $profileImage): self
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    // ========== NEW RELATIONSHIP METHODS ==========

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getUser() === $this) {
                $recipe->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setUser($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getUser() === $this) {
                $menu->setUser(null);
            }
        }

        return $this;
    }

    public function getNutritionProfile(): ?NutritionProfile
    {
        return $this->nutritionProfile;
    }

    public function setNutritionProfile(NutritionProfile $nutritionProfile): self
    {
        // set the owning side of the relation if necessary
        if ($nutritionProfile->getUser() !== $this) {
            $nutritionProfile->setUser($this);
        }

        $this->nutritionProfile = $nutritionProfile;
        return $this;
    }
}
