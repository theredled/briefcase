<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Services\DownloadService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(
        protected UriSigner $uriSigner,
        protected DownloadService $dlService,
    ) {
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
        $fields = [
            AssociationField::new('briefcase', 'Briefcase'),
            TextField::new('token', 'ID'),
            TextField::new('name', 'Titre'),
            ChoiceField::new('lang', 'Langue')->setChoices(['FR' => 'fr', 'EN' => 'en']),
            TextField::new('file', 'Fichier')->setFormType(VichFileType::class)
                //->setFormTypeOption('translation_domain', 'Blabla')
                ->setFormTypeOption('download_label', function (Document $doc) {return $doc->getFilename(); })
                //->setFormTypeOption('download_label', function (Document $doc) {return 'test';})
                ->hideOnIndex(),
            BooleanField::new('isFolder', 'Dossier?'),
            BooleanField::new('sensible', 'Sensible?'),
            DateTimeField::new('creationDate', 'Creé')->hideOnForm()->setFormat('dd/MM/yyyy'),
            DateTimeField::new('fileModificationDate', 'Modifié')->hideOnForm()->setFormat('dd/MM/yyyy'),
            AssociationField::new('includedFiles', 'Documents inclus')->setQueryBuilder(function (QueryBuilder $qb) {
                $qb->andWhere('entity.isFolder = FALSE');
            }),
        ];

        if (Crud::PAGE_INDEX === $pageName) {
            $fields = $this->reorderFields($fields, ['token', 'name', 'lang', 'isFolder', 'sensible', 'creationDate',
                'fileModificationDate', 'includedFiles', 'briefcase']);
        }

        return $fields;
    }

    protected function reorderFields(iterable $inputFields, $orderArray)
    {
        $keys = array_map(fn ($field) => $field->getAsDto()->getProperty(), $inputFields);
        $assoc = array_combine($keys, $inputFields);

        $outputFields = [];
        foreach ($orderArray as $fieldName) {
            $outputFields[$fieldName] = $assoc[$fieldName];
        }

        $extraFields = array_diff_key($outputFields, $inputFields);
        $outputFields = array_merge($outputFields, $extraFields);

        return $outputFields;
    }

    public function configureActions(Actions $actions): Actions
    {
        $copyLink = Action::new('copyLink', 'Lien', 'fa fa-link')
            ->setTemplatePath('main/copyLinkAction.html.twig')
            ->linkToUrl(function (Document $file) {
                return $this->dlService->getDownloadUrl($file);
            })
            ->renderAsLink();

        $actions->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->addCssClass('btn-secondary'));

        return $actions
            ->add(Crud::PAGE_INDEX, $copyLink)
            ->add(Crud::PAGE_EDIT, $copyLink)
            ->add(Crud::PAGE_DETAIL, $copyLink)
        ;
    }

    public function createEntity(string $entityFqcn): object
    {
        $entity = new Document();
        $entity->setCreationDate(new \DateTimeImmutable());
        $entity->setFileModificationDate(new \DateTimeImmutable());

        return $entity;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
    }

    /*protected function getDownloadUrl(Document $file): string
    {
        $url = $this->generateUrl('dl_anything', ['token' => $file->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($file->getSensible())
            $url = $this->uriSigner->sign($url);

        return $url;
    }*/
}
