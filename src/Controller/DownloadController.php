<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 02/05/2025
 * Time: 14:35
 */

namespace App\Controller;


use App\Entity\Download;
use App\Entity\Document;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Signature\Exception\InvalidSignatureException;
use \ZipArchive;

class DownloadController extends AbstractController
{

    #[Route('/', name: 'home')]
    #[Route('/dl/', name: 'dl_index')]
    public function dlIndex(Request $request, ManagerRegistry $doctrine): Response
    {
        $lang = $this->getLang($request);

        $fileEntities = $doctrine->getRepository(Document::class)->findNotSensible();

        foreach ($fileEntities as $item) {
            $item->faCssClass = $this->getFaCssClass($item);
        }

        return $this->render('main/dlIndex.html.twig', [
            'lang' => $request->getPreferredLanguage(['fr', 'en']),
            'items' => $fileEntities,
        ]);
    }

    #[Route('/', name: 'dl_cv', defaults: ['token' => 'cv_dev', 'dl' => 1,'inline' => 1], host: 'cv-benoit-guchet.fairyfiles.ovh')]
    #[Route('/dl/{lang}/{token}', name: 'dl_item_lang')]
    #[Route('/dl/{token}', name: 'dl_item')]
    #[Route('/do_dl_signed/{token}', name: 'do_dl_signed', defaults: ['dl' => 1, '_signed' => true])]
    #[Route('/dl_signed/{token}', name: 'dl_item_signed', defaults: ['_signed' => true])]
    public function dlItem($token, Request $request, ManagerRegistry $doctrine, UriSigner $uriSigner): Response
    {
        $fileEntity = $this->findEntity($token, $doctrine, $request, $uriSigner);

        //-- DL direct
        if ($request->get('dl') or $request->get('inline')) {
            $path = $this->getParameter('project_dir') . '/' . $fileEntity->getRelativePath();

            $this->registerDownload($fileEntity, $request, $doctrine);

            $response = new BinaryFileResponse($path, autoLastModified: false);
            $contentDisposition = 'inline';//$request->get('inline') ? 'inline' : 'attachment';
            $response->setContentDisposition($contentDisposition, $fileEntity->getDownloadFilename());
            return $response;
        }
        //-- Pour preview réseaux sociaux/chat
        else {
            return $this->render('main/dlPreview.html.twig', [
                'item' => $fileEntity,
                'dl_url' => $request->get('_signed')
                    ? $uriSigner->sign($this->generateUrl('dl_anything', ['token' => $token]))
                    : '?dl=1',
                'do_redirect' => true
            ]);
        }
    }


    #[Route('/fullPressKit', name: 'fullpresskit')]
    public function fullPressKit(Request $request, Filesystem $filesystem): Response
    {
        return $this->redirectToRoute('dl_folder', ['token' => 'fullpresskit']);
    }

    #[Route('/dlFolder/{token}', name: 'dl_folder')]
    #[Route('/dlFolder/{lang}/{token}', name: 'dl_folder_lang')]
    public function dlFolder($token, Request $request, Filesystem $filesystem, ManagerRegistry $doctrine): Response
    {
        $lang = $this->getLang($request, ['fr']);
        //var_dump(__METHOD__);exit;

        $document = $doctrine->getRepository(Document::class)->findOneBy(['token' => $token, 'lang' => $lang]);
        if (!$document)
            $document = $doctrine->getRepository(Document::class)->findOneBy(['token' => $token]);
        if (!$document)
            return $this->redirectToRoute('dl_index');

        $dirname = $token.'_'.$document->getLang();
        $path = $this->getParameter('web_dir').'/dl/'.$dirname;
        $filesInFolder = glob($path.'/*.*');
        $filesystem->mkdir($this->getParameter('zip_cache_dir'));
        $zipPath = $this->getParameter('zip_cache_dir').'/'.$dirname.'.zip';

        //-- Fichers dans le dossier + fichiers liés
        $allFiles = array_merge($filesInFolder, $document->getIncludedFiles()->toArray());
        $filesAreAhead = $this->filesAreAhead($zipPath, $allFiles, $document);

        if ($filesAreAhead) {
            $zip = new \ZipArchive();
            if (is_file($zipPath))
                unlink($zipPath);
            if ($ret = $zip->open($zipPath, \ZipArchive::CREATE) !== true)
                throw new \Exception('Erreur Zip : '.$ret.', '.$zip->getStatusString());
            $zip->addEmptyDir($dirname);
            if (!count($filesInFolder) and $document->getIncludedFiles()->count() == 0)
                throw new \Exception('No files in '.$path);
            foreach ($filesInFolder as $file)
                $zip->addFile($file, $dirname.'/'.basename($file));
            foreach ($document->getIncludedFiles() as $includedFile)
                $zip->addFile(
                    $this->getParameter('project_dir').'/'.$includedFile->getRelativePath(),
                    $dirname.'/'.basename($includedFile->getDownloadFilename())
                );
            $zip->close();
        }

        return new BinaryFileResponse($zipPath);
    }


    #[Route('/d/{token}', name: 'dl_anything')]
    #[Route('/d/{token}.{ext}', name: 'dl_anything_ext')]
    #[Route('/d/{lang}/{token}}', name: 'dl_anything_lang')]
    #[Route('/d/{lang}/{token}.{ext}', name: 'dl_anything_lang_ext')]
    public function dlAnything($token, Request $request, ManagerRegistry $doctrine, UriSigner $signer, Filesystem $filesystem): Response
    {
        $document = $this->findEntity($token, $doctrine, $request, $signer);

        if ($document->isFolder())
            return $this->dlFolder($token, $request, $filesystem, $doctrine);
        else
            return $this->dlItem($token, $request, $doctrine, $signer);
            //return $this->redirectToRoute('dl_item', ['token' => $token]);
    }

    protected function getLastModificationTimeInFiles(array $files)
    {
        $latestTime = null;
        foreach ($files as $file) {
            if ($file instanceof Document)
                $fileModTime = $file->getCalcFileModificationDate($this->getParameter('project_dir'))->getTimestamp();
            else
                $fileModTime = filemtime($file);

            if ($fileModTime > $latestTime)
                $latestTime = $fileModTime;
        }

        return $latestTime;
    }

    /**
     * @param object|Document $fileEntity
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return void
     */
    protected function registerDownload(Document $fileEntity, Request $request, ManagerRegistry $doctrine): void
    {
        $dl = new Download();
        $dl->setFile($fileEntity);
        $dl->setDate(new \DateTime());
        $dl->setIp($request->getClientIp());
        $dl->setInfos(json_encode($request->headers->all(), true));
        $dl->setFileName($fileEntity->getFilename());
        $dl->setFileModificationDate($fileEntity->getFileModificationDate());
        $em = $doctrine->getManager();
        $em->persist($dl);
        $em->flush();
    }

    protected function getFaCssClass(Document $fileEntity)
    {
        $defaultClass = 'fa-file-alt';

        if ($fileEntity->isFolder())
            return 'fa-file-archive';

        $absPath = $this->getParameter('project_dir').'/'.$fileEntity->getRelativePath();
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

    protected function getLang(Request $request, $available = ['fr', 'en'])
    {
        $lang = $request->get('lang', $request->getPreferredLanguage($available));
        if (in_array($lang, $available))
            return $lang;
        else
            return $available[0];
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param $token
     * @param mixed $lang
     * @return Document|object|null
     */
    protected function findEntity($token, ManagerRegistry $doctrine, Request $request, UriSigner $signer): null|Document
    {
        $lang = $this->getLang($request);

        $document = $doctrine->getRepository(Document::class)->findOneBy(['token' => $token, 'lang' => $lang]);
        if (!$document)
            $document = $doctrine->getRepository(Document::class)->findOneBy(['token' => $token]);

        if (!$document)
            throw $this->createNotFoundException('Fichier non trouvé : '.$token);
        if ($document->getSensible() and !$signer->checkRequest($request))
            throw new AccessDeniedHttpException('Lien non valide ou expiré : '.$token);

        return $document;
    }


    protected function filesAreAhead(string $zipPath, false|array $allFiles, Document $doc): bool
    {
        if (!is_file($zipPath))
            return true;
        elseif ($this->getLastModificationTimeInFiles($allFiles) > filemtime($zipPath))
            return true;
        elseif ($doc->getFileModificationDate() && $doc->getFileModificationDate()->getTimestamp() > filemtime($zipPath))
            return true;
        return false;
    }

}
