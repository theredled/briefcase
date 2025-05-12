<?php

namespace App\Http\Controllers;

use App\Entity\Download;
use App\Entity\DownloadableFile;
use App\Models\Document;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function index() {
        $documentList = Document::where('isSensible', false)->get();
        //$documentList = Document::all();

        foreach ($documentList as $document) {
            $document->faCssClass = $this->getFaCssClass($document);
        }

        /*return Inertia::render('DownloadsIndex', [
            'documents' => $documentList->toArray()
        ]);*/
        return view('downloads.index', [
            'documents' => $documentList
        ]);
    }

    public function download($documentToken, Request $request)
    {
        /** @var Document $doc */
        $doc = Document::where('token', $documentToken)->firstOrFail();

        if ($doc->isFolder)
            return $this->downloadFolder($doc, $request);
        else
            return $this->downloadFile($doc, $request);
    }


    public function testreact()
    {
        return Inertia::render('DownloadsTestreact');
    }

    public function testsvelte()
    {
        return Inertia::render('DownloadsTestsvelte');
    }


    protected function getFaCssClass(Document $doc)
    {
        $defaultClass = 'fa-file-alt';

        if ($doc->isFolder)
            return 'fa-file-archive';

        $absPath = $doc->getAbsolutePath();
        if (!is_file($absPath))
            return $defaultClass;
        $mimeType = $doc->getMimeType();

        if (!$mimeType)
            return $defaultClass;

        if ($mimeType == 'application/pdf')
            return 'fa-file-pdf';

        $mimePrefix = explode('/', $mimeType)[0];

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

    protected function downloadFolder(Document $doc)
    {
        $dirname = $doc->token;
        $path = $doc->getAbsolutePath();
        $filesInFolder = glob($path.'/*.*');
        //Storage::makeDirectory($this->getParameter('zip_cache_dir'));
        $zipFilename = $dirname.'.zip';
        $store = Cache::store('zip');
        $cacheDir = $store->getDirectory();
        File::makeDirectory($cacheDir, 0777, true, true);
        $zipPath = $cacheDir.'/'.$zipFilename;

        //-- Fichers dans le dossier + fichiers liés
        $allFiles = array_merge($filesInFolder, $doc->includedDocuments->all());
        $filesAreAhead = !is_file($zipPath)
            || $this->getLastModificationTimeInFiles($allFiles) > filemtime($zipPath)
            || $doc->getContentModificationDate()->getTimestamp() > filemtime($zipPath);

        if ($filesAreAhead) {
            $zip = new \ZipArchive();
            if (is_file($zipPath))
                unlink($zipPath);
            if ($ret = $zip->open($zipPath, \ZipArchive::CREATE) !== true)
                throw new \Exception('Erreur Zip : '.$ret.', '.$zip->getStatusString());
            $zip->addEmptyDir($dirname);
            if (!count($filesInFolder) and $doc->includedDocuments->count() == 0)
                throw new \Exception('No files in '.$path);
            foreach ($filesInFolder as $file)
                $zip->addFile($file, $dirname.'/'.basename($file));
            foreach ($doc->includedDocuments as $includedFile)
                $zip->addFile(
                    $includedFile->getAbsolutePath(),
                    $dirname.'/'.basename($includedFile->getDownloadFilename())
                );
            $zip->close();
        }

        return new BinaryFileResponse($zipPath);
    }


    protected function getLastModificationTimeInFiles(array $files)
    {
        $latestTime = null;
        foreach ($files as $file) {
            if ($file instanceof Document)
                $fileModTime = $file->getCalcContentModificationDate()->getTimestamp();
            else
                $fileModTime = filemtime($file);

            if ($fileModTime > $latestTime)
                $latestTime = $fileModTime;
        }

        return $latestTime;
    }


    /**
     * @param Document $doc
     * @return void
     */
    protected function registerDownload(Document $doc, Request $request): void
    {
        $dl = new \App\Models\Download();
        $dl->document_id = $doc->id;
        $dl->token = $doc->token;
        $dl->title = $doc->title;
        $dl->isFolder = $doc->isFolder;
        $dl->downloadDate = new \DateTime();
        $dl->ipAddress = $request->getClientIp();
        $dl->infos = json_encode(request()->headers->all(), true);
        $dl->storageFilename = $doc->storageFilename;
        $dl->originalFilename = $doc->originalFilename;
        $dl->contentModificationDate = $doc->contentModificationDate;
        $dl->save();
    }

    protected function downloadFile(Document $doc, Request $request)
    {
        //-- DL direct
        if ($request->get('dl') or $request->get('inline')) {
            $path = $doc->getAbsolutePath();

            $this->registerDownload($doc, $request);

            $response = response()->download($path);
            $response->setAutoLastModified(false);
            $contentDisposition = 'inline';//$request->get('inline') ? 'inline' : 'attachment';
            $response->setContentDisposition($contentDisposition, $doc->getDownloadFilename());
            return $response;
        }
        //-- Pour preview réseaux sociaux/chat
        else {
            return view('downloads.preview', [
                'doc' => $doc,
                'dl_url' => /*$request->get('_signed')
                    ? $urlSigner->sign($this->generateUrl('do_dl_signed', ['token' => $token]))
                    :*/ '?dl=1',
                'do_redirect' => false
            ]);
        }
    }
}
