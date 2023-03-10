<?php

namespace App\Entity;

use App\Repository\DownloadableFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DownloadableFileRepository::class)]
class DownloadableFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lang = 'fr';

    #[ORM\OneToMany(mappedBy: 'File', targetEntity: Download::class)]
    private Collection $Downloads;

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->Downloads = new ArrayCollection();
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
}
