<?php

namespace App\Models;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $storageFilename
 * @property string $originalFilename
 * @property string|null $mimetype
 * @property string $token
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $contentModificationDate
 * @property int $isFolder
 * @property int $isSensible
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Document> $includedDocuments
 * @property-read int|null $included_files_count
 */
class Document extends Model
{
    use TracksRelationChanges;

    protected function casts(): array
    {
        return [
            'contentModificationDate' => 'date',
        ];
    }

    protected $fillable = [
        'title',
        'token',
        'storageFilename',
        'originalFilename',
        'isFolder',
        'isSensible',
        'includedDocuments',
    ];

    public function getCalcContentModificationDate(): ?\DateTime
    {
        if ($this->contentModificationDate)
            return $this->contentModificationDate;

        $absPath = $this->getAbsolutePath();

        if (!file_exists($absPath))
            return null;
        return (new \DateTime())->setTimestamp(filemtime($absPath));

    }

    public function getRelativePath()
    {
        if ($this->isFolder)
            return 'app/private/folders/' . $this->token;
        elseif ($this->storageFilename)
            return 'app/private/documents/' . $this->storageFilename;
        else
            return null;
    }

    public function getAbsolutePath()
    {
        return storage_path($this->getRelativePath());
    }

    public function includedDocuments()
    {
        return $this->belongsToMany(Document::class, 'document_to_document', 'parentDocumentId', 'childDocumentId');
    }

    public function getExtension()
    {
        return pathinfo($this->storageFilename, PATHINFO_EXTENSION);
    }

    public function getRouteKeyName()
    {
        return 'token';
    }

    public function getMimeType()
    {
        $absPath = $this->getAbsolutePath();
        return is_file($absPath) ? mime_content_type($absPath) : null;
    }

    protected static function booted()
    {
        static::updating(function (Document $doc) {
            $changed = false;
            if ($doc->isDirty('storageFilename'))
                $doc->contentModificationDate = new \DateTime('now');

            /*f ($this->isFolder() and $this->hasRelationChanged('includedFiles')) {
                $removed = $this->getIncludedFiles()->getDeleteDiff();
                $inserted = $this->getIncludedFiles()->getInsertDiff();
                if ($removed || $inserted)
                    $changed = true;
            }

            if ($changed)
                $this->setContentModificationDate(new \DateTime('now'));*/
        });
    }

    public function getDownloadExtension()
    {
        if ($this->isFolder)
            $ext = 'zip';
        else
            $ext = $this->getExtension();
        return $ext;
    }

    public function getDownloadFilename()
    {
        $date = $this->contentModificationDate;
        return $this->title . ($date ? ' (' . $date->format('Ymd') . ')' : '') . '.' . $this->getDownloadExtension();
    }
}
