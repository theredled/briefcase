<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Api\DocumentProvider;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'downloadable_file')]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[UniqueEntity(
    fields: ['token', 'lang']
)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: [
            'groups' => ['document:list', 'document:detail'],
        ]),
        //new GetCollection(normalizationContext: ['groups' => ['document:list']])
    ],
    denormalizationContext: ['groups' => ['document:write']],
    provider: DocumentProvider::class
)]
class Document implements BelongsToBriefcase
{
    #[Groups(['bc:detail', 'document:list'])]
    public ?string $mimeType;

    #[Groups(['bc:detail', 'document:list'])]
    public ?string $faCssClass;

    #[Groups(['bc:detail', 'document:list'])]
    public ?bool $isValid;

    #[Groups(['bc:detail', 'document:list'])]
    public ?string $url;

    public function getFileModificationDate(): ?\DateTimeImmutable
    {
        return $this->fileModificationDate;
    }

    protected static ?string $dataDir;
    protected static ?string $foldersDir;

    public static function setDataDir($dir): void
    {
        self::$dataDir = $dir;
    }

    public static function setFoldersDir($dir): void
    {
        self::$foldersDir = $dir;
    }

    public function getDataDir(): ?string
    {
        if (!self::$dataDir)
            throw new Exception('Data dir non défini.');

        return self::$dataDir;
    }

    public function getFoldersDir(): ?string
    {
        if (!self::$foldersDir)
            throw new Exception('Folders dir non défini.');

        return self::$foldersDir;
    }

    public function getAbsolutePath(): string
    {
        return self::getDataDir().'/'.$this->getFileName();
    }

    public function getCalcFileModificationDate(): ?\DateTimeImmutable
    {
        if ($this->fileModificationDate)
            return $this->fileModificationDate;
        else {
            if (!$this->fileExists())
                return null;
            $absPath = $this->getAbsolutePath();

            return (new \DateTimeImmutable())->setTimestamp(filemtime($absPath));
        }
    }

    public function getFolderAbsolutePath(): string
    {
        return self::getFoldersDir().'/'.$this->getFolderName();
    }

    public function fileExists()
    {
        $absPath = $this->getAbsolutePath();
        return file_exists($absPath) && is_file($absPath);
    }

    public function folderExists()
    {
        $absPath = $this->getFolderAbsolutePath();
        return file_exists($absPath) && is_dir($absPath);
    }

    public function getFolderName()
    {
        $dirname = $this->getToken() . '_' . $this->getLang();
        return $dirname;
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
        if (!self::$dataDir)
            throw new Exception('Data dir non défini');
        return self::$dataDir;
        //return 'var/downloadable_files';
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bc:detail', 'document:list'])]
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
    #[Groups(['bc:detail', 'document:list'])]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    #[Groups(['bc:detail', 'document:list'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['bc:detail', 'document:list'])]
    private ?string $lang = 'fr';

    #[ORM\OneToMany(mappedBy: 'document', targetEntity: Download::class)]
    private Collection $downloads;

    #[ORM\Column(nullable: true)]
    #[Groups(['bc:detail', 'document:list'])]
    private ?bool $isFolder = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['bc:detail', 'document:list'])]
    private ?bool $sensible = false;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'documentContainers')]
    #[ORM\JoinTable(name: 'downloadable_file_downloadable_file')]
    #[ORM\JoinColumn(name: 'downloadable_file_target', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'downloadable_file_source', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['document:detail'])]
    #[SerializedName('included_documents')]
    private Collection $includedFiles;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'includedFiles')]
    private Collection $documentContainers;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Briefcase $briefcase = null;

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->downloads = new ArrayCollection();
        $this->includedFiles = new ArrayCollection();
        $this->documentContainers = new ArrayCollection();
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
        return $this->downloads;
    }

    public function addDownload(Download $download): self
    {
        if (!$this->downloads->contains($download)) {
            $this->downloads->add($download);
            $download->setDocument($this);
        }

        return $this;
    }

    public function removeDownload(Download $download): self
    {
        if ($this->downloads->removeElement($download)) {
            // set the owning side to null (unless already changed)
            if ($download->getDocument() === $this) {
                $download->setDocument(null);
            }
        }

        return $this;
    }

    public function getIsFolder(): ?bool
    {
        return $this->isFolder;
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
        return (bool)$this->sensible;
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
        return $this->includedFiles;
    }

    public function addIncludedFile(self $includedFile): static
    {
        if (!$this->includedFiles->contains($includedFile)) {
            $this->includedFiles->add($includedFile);
        }

        return $this;
    }

    public function removeIncludedFile(self $includedFile): static
    {
        $this->includedFiles->removeElement($includedFile);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getDocumentContainers(): Collection
    {
        return $this->documentContainers;
    }

    public function addDocumentContainer(self $downloadbleContainer): static
    {
        if (!$this->documentContainers->contains($downloadbleContainer)) {
            $this->documentContainers->add($downloadbleContainer);
            $downloadbleContainer->addIncludedFile($this);
        }

        return $this;
    }

    public function removeDocumentContainer(self $downloadbleContainer): static
    {
        if ($this->documentContainers->removeElement($downloadbleContainer)) {
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

    public function getBriefcase(): ?Briefcase
    {
        return $this->briefcase;
    }

    public function setBriefcase(?Briefcase $briefcase): static
    {
        $this->briefcase = $briefcase;

        return $this;
    }

}
