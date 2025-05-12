<?php

namespace App\Filament\Resources\DownloadableFileResource\Pages;

use App\Filament\Resources\DownloadableFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDownloadableFiles extends ListRecords
{
    protected static string $resource = DownloadableFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
