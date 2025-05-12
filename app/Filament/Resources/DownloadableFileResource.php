<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DownloadableFileResource\Pages;
use App\Filament\Resources\DownloadableFileResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DownloadableFileResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('token'),
                TextInput::make('title'),
                FileUpload::make('storageFilename')->disk('documents')->required()
                    ->getUploadedFileNameForStorageUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file) {
                      return sprintf('%s_%d.%s',
                          preg_replace('/[^a-z0-9._-]+/i', '-', $file->getClientOriginalName()),
                          random_int(1, 999),
                          $file->guessExtension()
                      );
                    })
                    ->preserveFilenames(false)
                    ->storeFileNamesIn('originalFilename')
                    /*->afterStateUpdated(function (UploadedFile $state, callable $set) {
                        if ($state) {
                            $set('originalFilename', $state->getClientOriginalName());
                        }
                    })*/,
                Checkbox::make('isFolder'),
                Checkbox::make('isSensible'),
                Forms\Components\Select::make('includedDocuments')->multiple()->relationship('includedDocuments', 'title')->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('token')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\CheckboxColumn::make('isFolder'),
                Tables\Columns\CheckboxColumn::make('isSensible'),
                Tables\Columns\TextColumn::make('contentModificationDate')->searchable()->sortable()->label('Last Modified'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDownloadableFiles::route('/'),
            'create' => Pages\CreateDownloadableFile::route('/create'),
            'edit' => Pages\EditDownloadableFile::route('/{record}/edit'),
        ];
    }
}
