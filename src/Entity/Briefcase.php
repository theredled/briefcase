<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Api\DocumentProvider;
use App\Repository\BriefcaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BriefcaseRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: [
            'groups' => ['bc:list', 'bc:detail'],
        ]),
        new GetCollection(normalizationContext: ['groups' => ['bc:list']])
    ],
    denormalizationContext: ['groups' => ['bc:write']],
)]
class Briefcase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bc:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['bc:detail'])]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'briefcases')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['bc:detail'])]
    private ?string $color = null;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'briefcase')]
    //#[Groups(['bc:detail'])]
    private Collection $documents;

    #[ORM\Column(length: 255)]
    #[Groups(['bc:detail'])]
    private ?string $token = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getName();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setBriefcase($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getBriefcase() === $this) {
                $document->setBriefcase(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }
}
