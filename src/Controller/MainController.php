<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\Document;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Spatie\UrlSigner\Support\Url;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    #[Route('/',
        name: 'home',
         condition: 'request.getHttpHost() != "cv-benoit-guchet.fairyfiles.ovh" && request.getHttpHost() != "fairyfiles.ovh"'
    )]
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

}
