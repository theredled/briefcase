<?php

namespace App\Controller\Admin;

use App\Entity\DownloadableFile;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
                ->setFileConstraints([]),
            BooleanField::new('isFolder') ,
            BooleanField::new('sensible'),
            DateTimeField::new('creationDate')->hideOnForm()->setFormat('dd/MM/yyyy'),
            DateTimeField::new('fileModificationDate')->hideOnForm()->setFormat('dd/MM/yyyy'),
            AssociationField::new('IncludedFiles')->setQueryBuilder(function(QueryBuilder $qb) {
                $qb->andWhere('entity.isFolder = FALSE');
            }),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewFile = Action::new('viewFile', 'Lien', 'fa fa-link')
            ->setTemplatePath('admin/copyLinkAction.html.twig')
            ->linkToUrl(function (DownloadableFile $file) {
                return $this->getDownloadUrl($file);
            })
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, $viewFile);
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new DownloadableFile();
        $entity->setCreationDate(new \DateTime('now'));
        $entity->setFileModificationDate(new \DateTime('now'));
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

    protected function getDownloadUrl(DownloadableFile $file): string
    {
        if ($file->getSensible()) {
            $url = $this->generateUrl('dl_item_signed', ['token' => $file->getToken()]);
            $url = $this->urlSigner->sign($url);
        } else
            $url = $this->generateUrl('dl_anything', [
                'token' => $file->getToken(),
                //'ext' => $file->getDownloadExtension()
            ]);

        return $url;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/admin.js')
            ->addCssFile('css/admin.css');
    }
}
