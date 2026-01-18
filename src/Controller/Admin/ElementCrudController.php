<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class ElementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Element::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('page', 'Seite')
                ->setHelp('Seite, der dieses Element zugeordnet ist (optional, leer = Startseite)'),
            AssociationField::new('elementType', 'Element Typ'),
            TextareaField::new('dataJson', 'Daten (JSON)')
                ->setHelp('Die Daten entsprechend der Feld-Definitionen des Element-Typs')
                ->setFormTypeOption('attr', ['rows' => 10]),
            IntegerField::new('sorting', 'Globale Sortierung')
                ->setHelp('Sortierung auf der Startseite (für Elemente ohne Seite)'),
            IntegerField::new('pageSorting', 'Seiten-Sortierung')
                ->setHelp('Sortierung innerhalb der zugeordneten Seite'),
            BooleanField::new('published', 'Veröffentlicht'),
        ];
    }
}