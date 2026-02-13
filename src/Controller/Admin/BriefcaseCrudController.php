<?php

namespace App\Controller\Admin;

use App\Entity\Briefcase;
use App\Entity\Document;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BriefcaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Briefcase::class;
    }

    public function createEntity(string $entityFqcn): object
    {
        $entity = new Briefcase();
        $entity->setUser($this->getUser());
        return $entity;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            TextField::new('token'),
            AssociationField::new('user'),
        ];
    }

}
