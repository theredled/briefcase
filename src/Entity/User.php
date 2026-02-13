<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, Briefcase>
     */
    #[ORM\OneToMany(targetEntity: Briefcase::class, mappedBy: 'user')]
    private Collection $briefcases;

    public function __construct()
    {
        $this->briefcases = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name ?? $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return Collection<int, Briefcase>
     */
    public function getBriefcases(): Collection
    {
        return $this->briefcases;
    }

    public function addBriefcase(Briefcase $briefcase): static
    {
        if (!$this->briefcases->contains($briefcase)) {
            $this->briefcases->add($briefcase);
            $briefcase->setUser($this);
        }

        return $this;
    }

    public function removeBriefcase(Briefcase $briefcase): static
    {
        if ($this->briefcases->removeElement($briefcase)) {
            // set the owning side to null (unless already changed)
            if ($briefcase->getUser() === $this) {
                $briefcase->setUser(null);
            }
        }

        return $this;
    }
}
