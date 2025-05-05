<?php

namespace App\Entity;

use App\Repository\DownloadableFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DownloadableFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DownloadableFile
{


    public function getFileModificationDate($project_dir = null): ?\DateTime
    {
        if ($this->fileModificationDate)
            return $this->fileModificationDate;
        elseif ($project_dir) {
            $absPath = $project_dir . '/' . self::getUploadDir() . '/' . $this->getFilename();
            if (!file_exists($absPath))
                return null;
            return (new \DateTime())->setTimestamp(filemtime($absPath));
        }
        else
            return new \DateTime('now');
    }
    #[ORM\PreUpdate]
    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $changed = false;
        if($eventArgs->hasChangedField('filename'))
            $changed = true;
        if ($this->isFolder() and $this->getIncludedFiles()->isDirty()) {
            $removed = $this->getIncludedFiles()->getDeleteDiff();
            $inserted = $this->getIncludedFiles()->getInsertDiff();
            if ($removed || $inserted)
                $changed = true;
        }

        if ($changed)
            $this->setFileModificationDate(new \DateTime('now'));
    }

    public function getDownloadExtension()
    {
        if ($this->isFolder())
            $ext = 'zip';
        else
            $ext = pathinfo($this->getFilename(), PATHINFO_EXTENSION);
        return $ext;
    }

    public function getDownloadFilename()
    {
        $date = $this->fileModificationDate;
        //return str_replace(' ', '-', $this->getName()).($date ? '-'.$date->format('Ymd') : '').'.'.$this->getDownloadExtension();
        return $this->getName().($date ? ' ('.$date->format('Ymd').')' : '').'.'.$this->getDownloadExtension();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $creationDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $fileModificationDate = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lang = 'fr';

    #[ORM\OneToMany(mappedBy: 'File', targetEntity: Download::class)]
    private Collection $Downloads;

    #[ORM\Column(nullable: true)]
    private ?bool $isFolder = false;

    #[ORM\Column(nullable: true)]
    private ?bool $sensible = false;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'Containers')]
    private Collection $IncludedFiles;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'IncludedFiles')]
    private Collection $Containers;

    public function __toString()
    {
        return $this->getName();
    }



    public function __construct()
    {
        $this->Downloads = new ArrayCollection();
        $this->IncludedFiles = new ArrayCollection();
        $this->DownloadableContainer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRelativePath()
    {
        return self::getUploadDir().'/'.$this->getFilename();
    }

    static public function getUploadDir() {
        return 'var/downloadable_files';
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang(?string $lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return Collection<int, Download>
     */
    public function getDownloads(): Collection
    {
        return $this->Downloads;
    }

    public function addDownload(Download $download): self
    {
        if (!$this->Downloads->contains($download)) {
            $this->Downloads->add($download);
            $download->setFile($this);
        }

        return $this;
    }

    public function removeDownload(Download $download): self
    {
        if ($this->Downloads->removeElement($download)) {
            // set the owning side to null (unless already changed)
            if ($download->getFile() === $this) {
                $download->setFile(null);
            }
        }

        return $this;
    }

    public function isFolder(): ?bool
    {
        return $this->isFolder;
    }

    public function setIsFolder(bool $isFolder): self
    {
        $this->isFolder = $isFolder;

        return $this;
    }

    public function getSensible()
    {
        return $this->sensible;
    }

    public function setSensible(?bool $sensible)
    {
        $this->sensible = $sensible;
        return $this;
    }


    public function setFileModificationDate(?\DateTime $fileModificationDate): void
    {
        $this->fileModificationDate = $fileModificationDate;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTime $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return Collection<int, self>
     */
    public function getIncludedFiles(): Collection
    {
        return $this->IncludedFiles;
    }

    public function addIncludedFile(self $includedFile): static
    {
        if (!$this->IncludedFiles->contains($includedFile)) {
            $this->IncludedFiles->add($includedFile);
        }

        return $this;
    }

    public function removeIncludedFile(self $includedFile): static
    {
        $this->IncludedFiles->removeElement($includedFile);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getDownloadableContainer(): Collection
    {
        return $this->DownloadableContainer;
    }

    public function addDownloadbleContainer(self $downloadbleContainer): static
    {
        if (!$this->DownloadableContainer->contains($downloadbleContainer)) {
            $this->DownloadableContainer->add($downloadbleContainer);
            $downloadbleContainer->addIncludedFile($this);
        }

        return $this;
    }

    public function removeDownloadbleContainer(self $downloadbleContainer): static
    {
        if ($this->DownloadableContainer->removeElement($downloadbleContainer)) {
            $downloadbleContainer->removeIncludedFile($this);
        }

        return $this;
    }
}
