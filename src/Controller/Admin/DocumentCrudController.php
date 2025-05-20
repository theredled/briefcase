<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(
        protected UriSigner $uriSigner,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['sensible' => 'DESC', 'token' => 'ASC'])
            ->setEntityLabelInSingular('Document')
            ->setEntityLabelInPlural('Documents')
           ;
    }

    #[Route('/admin_view_file/{filename}', name: 'admin_view_file')]
    public function viewFileAction($filename)
    {
        $path = Document::getUploadDir().'/'.$filename;
        return new BinaryFileResponse($path);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('token', 'ID'),
            TextField::new('name', 'Titre'),
            ChoiceField::new('lang', 'Langue')->setChoices(['FR' => 'fr', 'EN' => 'en']),
            TextField::new('file', 'Fichier')->setFormType(VichFileType::class)->hideOnIndex(),
            BooleanField::new('isFolder', 'Dossier?') ,
            BooleanField::new('sensible', 'Sensible?'),
            DateTimeField::new('creationDate', 'Creé')->hideOnForm()->setFormat('dd/MM/yyyy'),
            DateTimeField::new('fileModificationDate', 'Modifié')->hideOnForm()->setFormat('dd/MM/yyyy'),
            AssociationField::new('IncludedFiles', 'Documents inclus')->setQueryBuilder(function(QueryBuilder $qb) {
                $qb->andWhere('entity.isFolder = FALSE');
            }),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewFile = Action::new('viewFile', 'Lien', 'fa fa-link')
            ->setTemplatePath('main/copyLinkAction.html.twig')
            ->linkToUrl(function (Document $file) {
                return $this->getDownloadUrl($file);
            })
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, $viewFile)
            ->add(Crud::PAGE_EDIT, $viewFile)
            ->add(Crud::PAGE_DETAIL, $viewFile)
            ;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new Document();
        $entity->setCreationDate(new \DateTimeImmutable());
        $entity->setFileModificationDate(new \DateTimeImmutable());
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

    protected function getDownloadUrl(Document $file): string
    {
        $url = $this->generateUrl('dl_anything', ['token' => $file->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($file->getSensible())
            $url = $this->uriSigner->sign($url);

        return $url;
    }

}
