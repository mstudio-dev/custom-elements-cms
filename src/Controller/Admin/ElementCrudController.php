<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use App\Form\ElementDataType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class ElementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Element::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Element')
            ->setEntityLabelInPlural('Elemente')
            ->setDefaultSort(['sorting' => 'ASC'])
            ->setSearchFields(['id', 'elementType.name'])
            ->setPageTitle('index', 'Elemente verwalten');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('elementType', 'Element Typ')
            ->setRequired(true)
            ->setHelp('Typ des Inhaltselements');
        
        yield AssociationField::new('page', 'Seite')
            ->setHelp('Seite, der dieses Element zugeordnet ist (optional, leer = Startseite)');
        
        yield FormField::addPanel('Inhalte')->setIcon('fa fa-edit')->onlyOnForms();
        
        // Dynamische Felder basierend auf ElementType
        yield FormField::addFieldset('Elementdaten')
            ->setFormType(ElementDataType::class)
            ->onlyOnForms();
        
        // JSON-Fallback nur auf Index-Seite anzeigen
        yield TextareaField::new('dataJson', 'Daten (JSON)')
            ->onlyOnIndex()
            ->setMaxLength(100);
        
        yield FormField::addPanel('Einstellungen')->setIcon('fa fa-cog');
        yield IntegerField::new('sorting', 'Globale Sortierung')
            ->setHelp('Sortierung auf der Startseite (für Elemente ohne Seite)')
            ->setColumns(6);
        
        yield IntegerField::new('pageSorting', 'Seiten-Sortierung')
            ->setHelp('Sortierung innerhalb der zugeordneten Seite')
            ->setColumns(6);
        
        yield BooleanField::new('published', 'Veröffentlicht');
    }
}