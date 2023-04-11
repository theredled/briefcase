<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\DownloadableFile;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        return $this->render('main/index.html.twig', [
            'lang' => $this->getLang($request),
            'controller_name' => 'MainController',
        ]);
    }

    protected function getLang(Request $request, $available = ['fr', 'en'])
    {
        $lang = $request->get('lang', $request->getPreferredLanguage($available));
        if (in_array($lang, $available))
            return $lang;
        else
            return $available[0];
    }

    #[Route('/dl/{token}', name: 'dl_item')]
    public function dlItem($token, Request $request, ManagerRegistry $doctrine): Response
    {
        $lang = $this->getLang($request);

        $fileEntity = $doctrine->getRepository(DownloadableFile::class)->findOneBy(['token' => $token, 'lang' => $lang]);
        if (!$fileEntity)
            $fileEntity = $doctrine->getRepository(DownloadableFile::class)->findOneBy(['token' => $token]);
        if (!$fileEntity)
            return $this->redirectToRoute('dl_index');
            //throw $this->createAccessDeniedException('Type de fichier non trouvé : '.$token);

        $path = $this->getParameter('project_dir').'/'.$fileEntity->getRelativePath();

        $dl = new Download();
        $dl->setFile($fileEntity);
        $dl->setDate(new \DateTime());
        $dl->setIp($request->getClientIp());
        $dl->setInfos(json_encode($request->headers->all(), true));
        $em = $doctrine->getManager();
        $em->persist($dl);
        $em->flush();

        return new BinaryFileResponse($path, contentDisposition: 'attachment', autoLastModified: false);
    }

    #[Route('/dl/', name: 'dl_index')]
    public function dlIndex(Request $request, ManagerRegistry $doctrine): Response
    {
        $lang = $this->getLang($request);

        $fileEntities = $doctrine->getRepository(DownloadableFile::class)->findAll();

        return $this->render('main/dlIndex.html.twig', [
            'lang' => $request->getPreferredLanguage(['fr', 'en']),
            'items' => $fileEntities,
        ]);
    }

    #[Route('/fullPressKit', name: 'fullpresskit')]
    public function fullPressKit(Request $request, Filesystem $filesystem): Response
    {
        return $this->redirectToRoute('dl_folder', ['token' => 'fullpresskit']);
        /*$lang = $this->getLang($request, ['fr']);
        $dirname = 'full_press_kit_'.$lang;
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
            if (!count($files))
                throw new \Exception('No files in '.$path);
            foreach ($files as $file)
                $zip->addFile($file, basename($file));
            $zip->close();
        }

        return new BinaryFileResponse($zipPath);*/
    }
    #[Route('/dlFolder/{token}', name: 'dl_folder')]
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
            if (!count($files))
                throw new \Exception('No files in '.$path);
            foreach ($files as $file)
                $zip->addFile($file, basename($file));
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
}
