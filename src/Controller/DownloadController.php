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
use App\Services\DownloadService;
use Bg\MiscBundle\Helper\Url;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
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
    public function __construct(protected DownloadService $downloadService, protected ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'home')]
    #[Route('/dl/', name: 'dl_index')]
    public function dlIndex(Request $request, ManagerRegistry $doctrine): Response
    {
        $fileEntities = $doctrine->getRepository(Document::class)->findNotSensible();

        foreach ($fileEntities as $item) {
            $item->faCssClass = $this->downloadService->getFaCssClass($item);
            $item->isValid = $this->downloadService->checkDocumentValidity($item);
        }

        return $this->render('main/dlIndex.html.twig', [
            'lang' => $request->getPreferredLanguage(['fr', 'en']),
            'items' => $fileEntities,
        ]);
    }

    #[Route('/', name: 'dl_cv', defaults: ['token' => 'cv_dev', 'dl' => 1, 'inline' => 1], host: 'cv-benoit-guchet.fairyfiles.ovh')]
    #[Route('/d/{token}', name: 'dl_anything')]
    #[Route('/d/{token}.{ext}', name: 'dl_anything_ext')]
    #[Route('/d/{lang}/{token}}', name: 'dl_anything_lang')]
    #[Route('/d/{lang}/{token}.{ext}', name: 'dl_anything_lang_ext')]
    public function dlAnything($token, Request $request): Response
    {
        $lang= $this->getLang($request);

        $document = $this->downloadService->findEntityOrFail($token, $lang, $request);

        if ($request->get('dl') or $request->get('inline')) {

            $this->registerDownload($document, $request);

            if ($document->isFolder())
                return $this->dlFolder($document);
            else
                return $this->dlItem($document, $request);

            //-- Pour preview réseaux sociaux/chat
        } else {
            return $this->render('main/dlPreview.html.twig', [
                'item' => $document,
                'dl_url' => Url::changeUrlParams($request->getUri(), ['dl' => 1]),
                'do_redirect' => true
            ]);
        }
    }

    public function dlItem(Document $document, Request $request): Response
    {
        $path =  $document->getAbsolutePath();


        $response = new BinaryFileResponse($path, autoLastModified: false);
        $contentDisposition = 'inline';//$request->get('inline') ? 'inline' : 'attachment';
        $response->setContentDisposition($contentDisposition, $document->getDownloadFilename());
        return $response;
    }


    #[Route('/fullPressKit', name: 'fullpresskit')]
    public function fullPressKit(Request $request, Filesystem $filesystem): Response
    {
        return $this->redirectToRoute('dl_folder', ['token' => 'fullpresskit']);
    }

    public function dlFolder(Document $document): Response
    {
        $zipPath = $this->downloadService->buildZipFromFolder($document);

        return new BinaryFileResponse($zipPath);
    }


    /**
     * @param object|Document $fileEntity
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return void
     */
    protected function registerDownload(Document $fileEntity, Request $request): void
    {
        $dl = new Download();
        $dl->setFile($fileEntity);
        $dl->setDate(new \DateTime());
        $dl->setIp($request->getClientIp());
        $dl->setInfos(json_encode($request->headers->all(), true));
        $dl->setFileName($fileEntity->getFilename());
        $dl->setFileModificationDate($fileEntity->getFileModificationDate());
        $em = $this->doctrine->getManager();
        $em->persist($dl);
        $em->flush();
    }


    protected function getLang(Request $request, $available = ['fr', 'en'])
    {
        $lang = $request->get('lang', $request->getPreferredLanguage($available));
        if (in_array($lang, $available))
            return $lang;
        else
            return $available[0];
    }

}
