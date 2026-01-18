<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
            SlugField::new('slug', 'Slug')
                ->setTargetFieldName('title')
                ->setHelp('URL-freundlicher Name (wird automatisch aus dem Titel generiert)'),
            BooleanField::new('published', 'Veröffentlicht'),
            AssociationField::new('elements', 'Elemente')
                ->onlyOnDetail()
                ->setHelp('Elemente, die dieser Seite zugeordnet sind'),
            DateTimeField::new('createdAt', 'Erstellt am')
                ->hideOnForm(),
        ];
    }
}
