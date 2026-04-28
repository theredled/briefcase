<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 02/05/2025
 * Time: 14:35.
 */

namespace App\Controller;

use App\Entity\Briefcase;
use App\Entity\Document;
use App\Entity\Download;
use App\Exception\LinkExpiredException;
use App\Services\DownloadService;
use Bg\MiscBundle\Helper\Url;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Signature\Exception\ExpiredSignatureException;
use Vich\UploaderBundle\Form\Type\VichFileType;

class DownloadController extends BaseController
{
    public function __construct(protected DownloadService $downloadService, protected ManagerRegistry $doctrine)
    {
    }

    #[Route('/testForm/', name: 'testForm', env: ['dev', 'test'])]
    public function testForm(Request $request, ManagerRegistry $doctrine): Response
    {
        $doc = $doctrine->getRepository(Document::class)->find(30);


        $fb = $this->createFormBuilder($doc)
            ->add('file', VichFileType::class, [
                'download_uri' => 'foo',
                //'download_label' => fn($doc) => 'bar'
                'download_label' => 'test label'
            ]);

        return $this->render('testForm.html.twig', [
            'form' => $fb->getForm()->createView(),
        ]);
    }

    #[Route('/', name: 'home', env: ['dev', 'test'])]
    #[Route('/dl/', name: 'dl_index')]
    public function dlIndex(Request $request, ManagerRegistry $doctrine): Response
    {
        $bcToken = $this->getParameter('default_briefcase_token');

        return $this->redirectToRoute('viewBriefcase', ['token' => $bcToken]);
    }

    #[Route('/b/{token}', name: 'viewBriefcase', env: ['dev', 'test'])]
    public function viewBriefcase(Request $request, ManagerRegistry $doctrine, $token): Response
    {
        $briefcase = $doctrine->getRepository(Briefcase::class)->findOneBy(['token' => $token]);
        $this->denyAccessUnlessExists($briefcase);

        $documents = $doctrine->getRepository(Document::class)->findFromBriefcase($briefcase);

        foreach ($documents as $item) {
            $item->faCssClass = $this->downloadService->getFaCssClass($item);
            $item->isValid = $this->downloadService->checkDocumentValidity($item);
            $item->url = $this->downloadService->getDownloadUrl($item);
        }

        return $this->render('main/dlIndex.html.twig', [
            'lang' => $request->getPreferredLanguage(['fr', 'en']),
            'items' => $documents,
        ]);
    }

    #[Route('/', name: 'dl_cv', defaults: ['token' => 'cv_dev', 'dl' => 1, 'inline' => 1], host: 'cv-benoit-guchet.fairyfiles.ovh')]
    #[Route('/d/{token}', name: 'dl_anything')]
    #[Route('/d/{token}.{ext}', name: 'dl_anything_ext')]
    #[Route('/d/{lang}/{token}', name: 'dl_anything_lang')]
    #[Route('/d/{lang}/{token}.{ext}', name: 'dl_anything_lang_ext')]
    public function dlAnything($token, Request $request): Response
    {
        $lang = $this->getLang($request);

        try {
            $document = $this->downloadService->findEntityOrFail($token, $lang, $request);
        } catch (LinkExpiredException $e) {
            //throw new ExpiredSignatureException($e->getMessage(), 419, $e);
            //throw new AccessDeniedException($e->getMessage());
            //throw $this->createAccessDeniedException($e->getMessage(), $e);
            return $this->render('expired.html.twig', [], new Response(null, Response::HTTP_GONE));
        }


        if ($request->query->get('dl') or $request->query->get('inline')) {
            $this->registerDownload($document, $request);

                if ($document->isFolder()) {
                    return $this->dlFolder($document);
                }

                return $this->dlItem($document, $request);
        }

        // -- Pour preview réseaux sociaux/chat
        return $this->render('main/dlPreview.html.twig', [
            'item' => $document,
            'dl_url' => Url::changeUrlParams($request->getUri(), ['dl' => 1]),
            'do_redirect' => true,
        ]);
    }

    public function dlItem(Document $document, Request $request): Response
    {
        $path = $document->getAbsolutePath();

        $response = new BinaryFileResponse($path, autoLastModified: false);
        $contentDisposition = $document->getSensible() ? 'attachment' : 'inline'; // $request->get('inline') ? 'inline' : 'attachment';
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
     */
    protected function registerDownload(Document $fileEntity, Request $request): void
    {
        $dl = new Download();
        $dl->setDocument($fileEntity);
        $dl->setDate(new \DateTime());
        $dl->setIp($request->getClientIp());
        $dl->setInfos(json_encode($request->headers->all(), true));
        $dl->setFileName($fileEntity->getFilename());
        $dl->setFileModificationDate($fileEntity->getFileModificationDate()
            ? \DateTime::createFromImmutable($fileEntity->getFileModificationDate()) : null);
        $em = $this->doctrine->getManager();
        $em->persist($dl);
        $em->flush();
    }

    protected function getLang(Request $request, $available = ['fr', 'en'])
    {
        $lang = $request->query->get('lang', $request->getPreferredLanguage($available));
        if (in_array($lang, $available)) {
            return $lang;
        }

        return $available[0];
    }
}
