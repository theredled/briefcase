<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'downloadable_file')]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[UniqueEntity(
    fields: ['token', 'lang']
)]
class Document
{
    public ?string $mimeType;
    public ?string $faCssClass;

    public function getFileModificationDate(): ?\DateTimeImmutable
    {
        return $this->fileModificationDate;
    }

    protected static ?string $dataDir;

    public static function setDataDir($dir): void
    {
        self::$dataDir = $dir;
    }

    public function getDataDir(): ?string
    {
        return self::$dataDir;
    }

    public function getAbsolutePath(): string
    {
        if (!self::$dataDir)
            throw new Exception('Data dir non défini.');
        return self::$dataDir.'/'.$this->getFileName();
    }

    public function getCalcFileModificationDate(): ?\DateTimeImmutable
    {
        if ($this->fileModificationDate)
            return $this->fileModificationDate;
        elseif ($this->getDataDir()) {
            $absPath = $this->getDataDir() . '/' . $this->getFilename();
            if (!file_exists($absPath))
                return null;
            return (new \DateTimeImmutable())->setTimestamp(filemtime($absPath));
        }
        else
            return new \DateTimeImmutable('now');
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
            $this->setFileModificationDate(new \DateTimeImmutable());
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

    public function getRelativePath()
    {
        return self::getUploadDir().'/'.$this->getFilename();
    }

    static public function getUploadDir() {
        if (self::$dataDir)
            throw new Exception('Data dir non défini');
        return self::$dataDir;
        //return 'var/downloadable_files';
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**#[ORM\Column(length: 255, nullable: true)]*/
    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'filename')]
    private ?File $file = null;

    #[ORM\Column(nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $creationDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $fileModificationDate = null;

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
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'DocumentContainers')]
    #[ORM\JoinTable(name: 'downloadable_file_downloadable_file')]
    #[ORM\JoinColumn(name: 'downloadable_file_target', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'downloadable_file_source', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $IncludedFiles;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'IncludedFiles')]
    private Collection $DocumentContainers;

    public function __toString()
    {
        return $this->getName();
    }



    public function __construct()
    {
        $this->Downloads = new ArrayCollection();
        $this->IncludedFiles = new ArrayCollection();
        $this->DocumentContainers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
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


    public function setFileModificationDate(?\DateTimeImmutable $fileModificationDate): void
    {
        $this->fileModificationDate = $fileModificationDate;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeImmutable $creationDate): void
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
    public function getDocumentContainers(): Collection
    {
        return $this->DocumentContainers;
    }

    public function addDocumentContainer(self $downloadbleContainer): static
    {
        if (!$this->DocumentContainers->contains($downloadbleContainer)) {
            $this->DocumentContainers->add($downloadbleContainer);
            $downloadbleContainer->addIncludedFile($this);
        }

        return $this;
    }

    public function removeDocumentContainer(self $downloadbleContainer): static
    {
        if ($this->DocumentContainers->removeElement($downloadbleContainer)) {
            $downloadbleContainer->removeIncludedFile($this);
        }

        return $this;
    }


    public function getFile(): UploadedFile|File|null
    {
        return $this->file;
    }

    public function setFile(UploadedFile|File|null $file)
    {
        $this->file = $file;


        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->fileModificationDate = new \DateTimeImmutable();
        }
    }
}
