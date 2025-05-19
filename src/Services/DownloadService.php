<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 19/05/2025
 * Time: 15:11
 */

namespace App\Services;


use App\Entity\Document;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class DownloadService implements EventSubscriberInterface
{
    public function __construct(protected $zipsDir, protected $foldersDir, protected $filesDir,
                                protected Filesystem $filesystem,
                                protected ManagerRegistry $doctrine,
                                protected UriSigner $uriSigner)
    {
        Document::setDataDir($filesDir);
    }

    public function buildZipFromFolder(Document $document)
    {
        $dirname = $document->getToken() . '_' . $document->getLang();
        $path = $this->filesDir . '/' . $dirname;
        $filesInFolder = glob($path . '/*.*');
        $this->filesystem->mkdir($this->zipsDir);
        $zipPath = $this->zipsDir . '/' . $dirname . '.zip';

        //-- Fichers dans le dossier + fichiers liés
        $allFiles = array_merge($filesInFolder, $document->getIncludedFiles()->toArray());
        $filesAreAhead = $this->filesAreAhead($zipPath, $allFiles, $document);

        if ($filesAreAhead) {
            $zip = new \ZipArchive();
            if (is_file($zipPath))
                unlink($zipPath);
            if ($ret = $zip->open($zipPath, \ZipArchive::CREATE) !== true)
                throw new \Exception('Erreur Zip : ' . $ret . ', ' . $zip->getStatusString());
            $zip->addEmptyDir($dirname);
            if (!count($filesInFolder) and $document->getIncludedFiles()->count() == 0)
                throw new \Exception('No files in ' . $path);
            foreach ($filesInFolder as $file)
                $zip->addFile($file, $dirname . '/' . basename($file));
            foreach ($document->getIncludedFiles() as $includedFile)
                $zip->addFile(
                    $this->filesDir . '/' . $includedFile->getFilename(),
                    $dirname . '/' . basename($includedFile->getDownloadFilename())
                );
            $zip->close();
        }

        return $zipPath;
    }

    protected function getLastModificationTimeInFiles(array $files)
    {
        $latestTime = null;
        foreach ($files as $file) {
            if ($file instanceof Document)
                $fileModTime = $file->getCalcFileModificationDate()->getTimestamp();
            else
                $fileModTime = filemtime($file);

            if ($fileModTime > $latestTime)
                $latestTime = $fileModTime;
        }

        return $latestTime;
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param $token
     * @param mixed $lang
     * @return Document|object|null
     */
    public function findEntityOrFail($token, ?string $lang, Request $request): null|Document
    {
        $document = $this->doctrine->getRepository(Document::class)->findOneBy(['token' => $token, 'lang' => $lang]);
        if (!$document)
            $document = $this->doctrine->getRepository(Document::class)->findOneBy(['token' => $token]);

        if (!$document)
            throw new NotFoundHttpException('Fichier non trouvé : ' . $token);
        if ($document->getSensible() and !$this->uriSigner->checkRequest($request))
            throw new AccessDeniedHttpException('Lien non valide ou expiré : ' . $token);

        return $document;
    }


    public function filesAreAhead(string $zipPath, false|array $allFiles, Document $doc): bool
    {
        if (!is_file($zipPath))
            return true;
        elseif ($this->getLastModificationTimeInFiles($allFiles) > filemtime($zipPath))
            return true;
        elseif ($doc->getFileModificationDate() && $doc->getFileModificationDate()->getTimestamp() > filemtime($zipPath))
            return true;
        return false;
    }


    public function getFaCssClass(Document $fileEntity)
    {
        $defaultClass = 'fa-file-alt';

        if ($fileEntity->isFolder())
            return 'fa-file-archive';

        $absPath = $fileEntity->getAbsolutePath();
        if (!is_file($absPath))
            return $defaultClass;
        $fileEntity->mimeType = mime_content_type($absPath);

        if (!$fileEntity->mimeType)
            return $defaultClass;

        if ($fileEntity->mimeType == 'application/pdf')
            return 'fa-file-pdf';

        $mimePrefix = explode('/', $fileEntity->mimeType)[0];

        $mimePrefixesToClasses = [
            'image' => 'fa-file-image',
            'text' => 'fa-file-alt',
            'video' => 'fa-file-video',
            'audio' => 'fa-file-audio',
        ];

        if (isset($mimePrefixesToClasses[$mimePrefix]))
            return $mimePrefixesToClasses[$mimePrefix];

        return $defaultClass;
    }


    static public function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [
                ['setupAbsoluteDirs', 1]
            ]
        ];
    }

    public function setupAbsoluteDirs(RequestEvent $event) {
        Document::setDataDir($this->filesDir);
    }
}
