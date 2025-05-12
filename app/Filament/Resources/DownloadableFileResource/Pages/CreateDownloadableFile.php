<?php

namespace App\Filament\Resources\DownloadableFileResource\Pages;

use App\Filament\Resources\DownloadableFileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadableFile extends CreateRecord
{
    protected static string $resource = DownloadableFileResource::class;
}
