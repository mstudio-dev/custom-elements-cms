<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Seite')
            ->setEntityLabelInPlural('Seiten')
            ->setSearchFields(['title', 'slug'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published', 'Veröffentlicht'))
            ->add(TextFilter::new('title', 'Titel'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();
        
        yield TextField::new('title', 'Titel')
            ->setColumns(6)
            ->setHelp('Hauptüberschrift der Seite');
        
        yield SlugField::new('slug', 'URL-Pfad')
            ->setTargetFieldName('title')
            ->setColumns(6)
            ->setHelp('Wird automatisch aus dem Titel generiert');
        
        yield BooleanField::new('published', 'Veröffentlicht')
            ->setHelp('Nur veröffentlichte Seiten sind im Frontend sichtbar');
        
        yield TextField::new('metaTitle', 'SEO: Meta Title')
            ->setColumns(12)
            ->setHelp('Seitentitel für Suchmaschinen (max. 60 Zeichen). Leer = Titel wird verwendet')
            ->hideOnIndex();
        
        yield TextareaField::new('metaDescription', 'SEO: Meta Description')
            ->setColumns(12)
            ->setHelp('Beschreibung für Suchmaschinen (max. 160 Zeichen)')
            ->hideOnIndex();
        
        yield AssociationField::new('elements', 'Zugeordnete Elemente')
            ->onlyOnDetail()
            ->setTemplatePath('admin/field/page_elements.html.twig');
        
        yield DateTimeField::new('createdAt', 'Erstellt am')
            ->setFormat('dd.MM.yyyy HH:mm')
            ->onlyOnDetail();
    }
}
