<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 02/05/2025
 * Time: 14:35
 */

namespace App\Controller;


use App\Entity\Download;
use App\Entity\DownloadableFile;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    #[Route('/', name: 'dl_cv', defaults: ['token' => 'cv_dev', 'dl' => 1,'inline' => 1], host: 'cv-benoit-guchet.fairyfiles.ovh')]
    #[Route('/dl/{lang}/{token}', name: 'dl_item_lang')]
    #[Route('/dl/{token}', name: 'dl_item')]
    #[Route('/do_dl_signed/{token}', name: 'do_dl_signed', defaults: ['dl' => 1, '_signed' => true])]
    #[Route('/dl_signed/{token}', name: 'dl_item_signed', defaults: ['_signed' => true])]
    public function dlItem($token, Request $request, ManagerRegistry $doctrine, UrlSignerInterface $urlSigner): Response
    {
        $lang = $this->getLang($request);

        $fileEntity = $doctrine->getRepository(DownloadableFile::class)->findOneBy(['token' => $token, 'lang' => $lang]);
        if (!$fileEntity)
            $fileEntity = $doctrine->getRepository(DownloadableFile::class)->findOneBy(['token' => $token]);
        if (!$fileEntity)
            throw $this->createAccessDeniedException('Fichier non trouvé : '.$token);
        if ($fileEntity->getSensible() and !$request->get('_signed'))
            throw $this->createAccessDeniedException('Lien expiré : '.$token);

        //-- DL direct
        if ($request->get('dl') or $request->get('inline')) {
            $path = $this->getParameter('project_dir') . '/' . $fileEntity->getRelativePath();

            $this->registerDownload($fileEntity, $request, $doctrine);

            $response = new BinaryFileResponse($path, autoLastModified: false);
            $response->setContentDisposition(($request->get('inline') ? 'inline' : 'attachment'), $fileEntity->getDownloadFilename());
            return $response;
        }
        //-- Pour preview réseaux sociaux/chat
        else {
            return $this->render('main/dlPreview.html.twig', [
                'item' => $fileEntity,
                'dl_url' => $request->get('_signed')
                    ? $urlSigner->sign($this->generateUrl('do_dl_signed', ['token' => $token]))
                    : '?dl=1',
                'do_redirect' => true
            ]);
        }
    }

    #[Route('/', name: 'dl_fairyfiles_index', host: '*.fairyfiles.ovh')]
    #[Route('/dl/', name: 'dl_index')]
    public function dlIndex(Request $request, ManagerRegistry $doctrine): Response
    {
        $lang = $this->getLang($request);

        $fileEntities = $doctrine->getRepository(DownloadableFile::class)->findNotSensible();

        foreach ($fileEntities as $item) {
            $item->faCssClass = $this->getFaCssClass($item);
        }

        return $this->render('main/dlIndex.html.twig', [
            'lang' => $request->getPreferredLanguage(['fr', 'en']),
            'items' => $fileEntities,
        ]);
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

        $fileEntity = $doctrine->getRepository(DownloadableFile::class)->findOneBy(['token' => $token, 'lang' => $lang]);
        if (!$fileEntity)
            $fileEntity = $doctrine->getRepository(DownloadableFile::class)->findOneBy(['token' => $token]);
        if (!$fileEntity)
            return $this->redirectToRoute('dl_index');

        $dirname = $token.'_'.$fileEntity->getLang();
        $path = $this->getParameter('web_dir').'/dl/'.$dirname;
        $files = glob($path.'/*.*');
        $filesystem->mkdir($this->getParameter('zip_cache_dir'));
        $zipPath = $this->getParameter('zip_cache_dir').'/'.$dirname.'.zip';

        if (!is_file($zipPath) or $this->getLastModificationTimeInFiles($files) > filemtime($zipPath)) {
            $zip = new \ZipArchive();
            if (is_file($zipPath))
                unlink($zipPath);
            if ($ret = $zip->open($zipPath, \ZipArchive::CREATE) !== true)
                throw new \Exception('Erreur Zip : '.$ret.', '.$zip->getStatusString());
            $zip->addEmptyDir($dirname);
            if (!count($files))
                throw new \Exception('No files in '.$path);
            foreach ($files as $file)
                $zip->addFile($file, $dirname.'/'.basename($file));
            $zip->close();
        }

        return new BinaryFileResponse($zipPath);
    }

    protected function getLastModificationTimeInFiles($files)
    {
        $lastTime = null;
        foreach ($files as $file)
            if (filemtime($file) > $lastTime)
                $lastTime = filemtime($file);

        return $lastTime;
    }

    /**
     * @param object|DownloadableFile $fileEntity
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return void
     */
    protected function registerDownload(DownloadableFile $fileEntity, Request $request, ManagerRegistry $doctrine): void
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

    protected function getFaCssClass(DownloadableFile $fileEntity)
    {
        $defaultClass = 'fa-file-alt';

        if ($fileEntity->isFolder())
            return 'fa-file-archive';

        $absPath = $this->getParameter('project_dir').'/'.$fileEntity->getRelativePath();
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

}
