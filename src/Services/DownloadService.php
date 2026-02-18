<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 19/05/2025
 * Time: 15:11
 */

namespace App\Services;


use App\Entity\Document;
use Bg\MiscBundle\Helper\Url;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class DownloadService implements EventSubscriberInterface
{
    public function __construct(protected $zipsDir, protected $foldersDir, protected $filesDir,
                                #[Autowire('%download_url_prefix%')] protected string $downloadUrlPrefix,
                                protected Filesystem $filesystem,
                                protected ManagerRegistry $doctrine,
                                protected UriSigner $uriSigner,
                                protected RouterInterface $router)
    {
        Document::setDataDir($filesDir);
    }

    public function findSimpleFilesFromFolder(Document $document)
    {
        $path = $document->getFolderAbsolutePath();
        $filesInFolder = glob($path . '/*.*');
        return $filesInFolder;
    }

    public function buildZipFromFolder(Document $document)
    {
        $path = $document->getFolderAbsolutePath();
        $dirname = basename($path);
        $filesInFolder = $this->findSimpleFilesFromFolder($document);
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
        if ($document->getSensible() and !$this->uriSignerCheckRequest($request))
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

    public function checkDocumentValidity(Document $doc)
    {
        if ($doc->isFolder()) {
            return $doc->folderExists() || $doc->getIncludedFiles()->count() > 0;
        }

        return $doc->fileExists();
    }


    public function getFaCssClass(Document $fileEntity)
    {
        return 'fa-'.$this->getFontAwesomeIconName($fileEntity);
    }

    public function getFontAwesomeIconName(Document|string $pathOrDocument): string
    {
        $defaultClass = 'file-alt';

        //-- path
        if (is_string($pathOrDocument)) {
            $absPath = $pathOrDocument;
        }
        else {
            if ($pathOrDocument->isFolder())
                return 'folder-open';

            $absPath = $pathOrDocument->getAbsolutePath();
        }

        $mimeType = $this->findMimeType($absPath);

        if (!$mimeType)
            return $defaultClass;

        if ($mimeType == 'application/pdf')
            return 'file-pdf';

        $mimePrefix = explode('/', $mimeType)[0];

        $mimePrefixesToClasses = [
            'image' => 'file-image',
            'text' => 'file-alt',
            'video' => 'file-video',
            'audio' => 'file-audio',
        ];

        if (isset($mimePrefixesToClasses[$mimePrefix]))
            return $mimePrefixesToClasses[$mimePrefix];

        return $defaultClass;
    }

    protected function findMimeType($fileName)
    {
        if (is_file($fileName))
             return mime_content_type($fileName);;

        $extToMimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpg',
            'txt' => 'text/plain',
            'mp4' => 'video/mp4',
            'avi' => 'video/avi',
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'flac' => 'audio/flac',
        ];

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return $extToMimeTypes[$ext] ?? null;
    }

    public function uriSignerCheckRequest(Request $request): bool
    {
        $qs = ($qs = $request->server->get('QUERY_STRING')) ? '?'.$qs : '';
        $uri = $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo().$qs;
        $uri = Url::changeUrlParams($uri, [], ['dl']);

        return $this->uriSigner->check($uri);
    }

    static public function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [
                ['setupAbsoluteDirs', 100]
            ]
        ];
    }

    public function setupAbsoluteDirs(RequestEvent $event) {
        Document::setDataDir($this->filesDir);
        Document::setFoldersDir($this->foldersDir);
    }

    public function getDownloadUrl(Document $file): string
    {
        $urlPath = $this->router->generate('dl_anything', ['token' => $file->getToken()], UrlGeneratorInterface::ABSOLUTE_PATH);
        $url = $this->downloadUrlPrefix . $urlPath;

        if ($file->getSensible())
            $url = $this->uriSigner->sign($url);

        return $url;
    }
}
