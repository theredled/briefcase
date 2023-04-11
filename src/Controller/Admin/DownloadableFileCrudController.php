<?php

namespace App\Controller\Admin;

use App\Entity\DownloadableFile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DownloadableFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DownloadableFile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('token'),
            TextField::new('name'),
            ChoiceField::new('lang')->setChoices(['FR' => 'fr', 'EN' => 'en']),
            ImageField::new('filename')->setUploadDir(DownloadableFile::getUploadDir()),
            BooleanField::new('isFolder')
        ];
    }
}
