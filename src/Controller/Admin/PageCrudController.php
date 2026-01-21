<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PageCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }
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

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplizieren', 'fa fa-copy')
            ->linkToCrudAction('duplicatePage')
            ->setCssClass('btn btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate);
    }

    public function duplicatePage(): RedirectResponse
    {
        $originalPage = $this->getContext()->getEntity()->getInstance();
        
        $newPage = new Page();
        $newPage->setTitle($originalPage->getTitle() . ' (Kopie)');
        $newPage->setSlug($originalPage->getSlug() . '-kopie');
        $newPage->setContent($originalPage->getContent());
        $newPage->setMetaDescription($originalPage->getMetaDescription());
        $newPage->setMetaTitle($originalPage->getMetaTitle());
        $newPage->setPublished(false);
        $newPage->setShowInNavigation($originalPage->isShowInNavigation());
        $newPage->setParent($originalPage->getParent());
        
        $this->entityManager->persist($newPage);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Seite wurde erfolgreich dupliziert.');
        
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($newPage->getId())
            ->generateUrl();
        
        return new RedirectResponse($url);
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
