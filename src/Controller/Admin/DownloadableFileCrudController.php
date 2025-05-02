<?php

namespace App\Controller\Admin;

use App\Entity\DownloadableFile;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\File;

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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['sensible' => 'DESC', 'token' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('token'),
            TextField::new('name'),
            ChoiceField::new('lang')->setChoices(['FR' => 'fr', 'EN' => 'en']),
            ImageField::new('filename')->setUploadDir(DownloadableFile::getUploadDir())
                ->hideOnIndex()
                ->hideOnDetail()
                ->setFileConstraints([]),
            BooleanField::new('isFolder'),
            BooleanField::new('sensible'),
            DateTimeField::new('creationDate')->hideOnForm(),
            DateTimeField::new('fileModificationDate')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewFile = Action::new('viewFile', 'Lien', 'fa fa-link')
            ->linkToUrl(function (DownloadableFile $file) {
                if ($file->getSensible()) {
                    $url = $this->generateUrl('dl_item_signed', ['token' => $file->getToken()]);
                    $url = $this->urlSigner->sign($url);
                }
                elseif ($file->isFolder())
                    $url = $this->generateUrl('dl_folder', ['token' => $file->getToken()]);
                else
                    $url = $this->generateUrl('dl_item', ['token' => $file->getToken()]);
                return $url;
            })
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, $viewFile)
        ;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new DownloadableFile();
        $entity->setCreationDate(new \DateTime('now'));
        return $entity;
    }

    public function updateEntity(EntityManagerInterface $em, $entity): void
    {
        parent::updateEntity($em, $entity);
    }

    public function persistEntity(EntityManagerInterface $em, $entity): void
    {
        parent::persistEntity($em, $entity);
    }
}
