<?php

namespace App\Controller\Admin;

use App\Entity\Download;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DownloadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Download::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            DateTimeField::new('date')->setFormat('dd/MM/yyyy HH:mm'),
            AssociationField::new('File', 'Document'),
            TextField::new('fileName', 'Fichier original'),
            DateTimeField::new('fileModificationDate', 'Date fichier')->setFormat('dd/MM/yyyy'),
            TextField::new('ip'),
            TextareaField::new('infos')->onlyOnDetail(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ;
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud->setDefaultSort(['id' => 'DESC'])
            ->setEntityLabelInSingular('Téléchargement')
            ->setEntityLabelInPlural('Téléchargements')
            ->setPageTitle(Crud::PAGE_INDEX, 'Historique des téléchargements');
    }

}
