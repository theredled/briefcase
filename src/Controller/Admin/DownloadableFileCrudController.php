<?php

namespace App\Controller\Admin;

use App\Entity\DownloadableFile;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DownloadableFileCrudController extends AbstractCrudController
{
    public function __construct(
        private UrlSignerInterface $urlSigner,
    )
    {
    }

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
            BooleanField::new('isFolder'),
            BooleanField::new('sensible')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewFile = Action::new('viewFile', 'Lien de téléchargement')
            ->linkToUrl(function (DownloadableFile $file) {
                if ($file->getSensible()) {
                    $url = $this->generateUrl('dl_item_signed', ['token' => $file->getToken()]);
                    $url = $this->urlSigner->sign($url);
                }
                else
                    $url = $this->generateUrl('dl_item', ['token' => $file->getToken()]);
                return $url;
            })
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, $viewFile)
        ;
    }
}
