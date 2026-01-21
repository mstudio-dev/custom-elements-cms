<?php

namespace App\Controller\Admin;

use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NewsCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }
    public static function getEntityFqcn(): string
    {
        return News::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('News')
            ->setEntityLabelInPlural('News')
            ->setSearchFields(['title', 'content'])
            ->setDefaultSort(['publishedAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplizieren', 'fa fa-copy')
            ->linkToCrudAction('duplicateNews');

        return $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate);
    }

    public function duplicateNews(): RedirectResponse
    {
        $originalNews = $this->getContext()->getEntity()->getInstance();
        
        $newNews = new News();
        $newNews->setTitle($originalNews->getTitle() . ' (Kopie)');
        $newNews->setSlug($originalNews->getSlug() . '-kopie');
        $newNews->setContent($originalNews->getContent());
        $newNews->setExcerpt($originalNews->getExcerpt());
        $newNews->setImage($originalNews->getImage());
        $newNews->setMetaDescription($originalNews->getMetaDescription());
        $newNews->setMetaTitle($originalNews->getMetaTitle());
        $newNews->setStatus('draft');
        $newNews->setFeatured($originalNews->isFeatured());
        $newNews->setAuthor($originalNews->getAuthor());
        
        foreach ($originalNews->getCategories() as $category) {
            $newNews->addCategory($category);
        }
        
        $this->entityManager->persist($newNews);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'News wurde erfolgreich dupliziert.');
        
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($newNews->getId())
            ->generateUrl();
        
        return new RedirectResponse($url);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('status', 'Status'))
            ->add(BooleanFilter::new('featured', 'Highlight'))
            ->add(TextFilter::new('title', 'Titel'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();
        
        yield TextField::new('title', 'Titel')
            ->setColumns(8)
            ->setHelp('Hauptüberschrift des News-Artikels');
        
        yield SlugField::new('slug', 'URL-Pfad')
            ->setTargetFieldName('title')
            ->setColumns(4);
        
        yield TextareaField::new('content', 'Inhalt')
            ->setNumOfRows(10)
            ->setHelp('Vollständiger Artikeltext. Die ersten 300 Zeichen werden als Teaser verwendet.')
            ->hideOnIndex();
        
        yield ArrayField::new('imageIds', 'Bild-IDs')
            ->setHelp('Liste der Media-IDs, z.B. [1, 5, 8]. Das erste Bild ist das Teaserbild (max. 6 Bilder)')
            ->hideOnIndex();
        
        yield TextField::new('metaTitle', 'SEO: Meta Title')
            ->setColumns(12)
            ->setHelp('Seitentitel für Suchmaschinen (max. 60 Zeichen). Leer = Titel wird verwendet')
            ->hideOnIndex();
        
        yield TextareaField::new('metaDescription', 'SEO: Meta Description')
            ->setColumns(12)
            ->setHelp('Beschreibung für Suchmaschinen (max. 160 Zeichen). Leer = Teaser wird verwendet')
            ->hideOnIndex();
        
        yield TextField::new('author', 'Autor')
            ->setColumns(6)
            ->hideOnIndex();
        
        yield DateTimeField::new('publishedAt', 'Veröffentlicht am')
            ->setFormat('dd.MM.yyyy HH:mm')
            ->setColumns(6)
            ->setHelp('Wird automatisch gesetzt wenn Status auf "published" geändert wird');
        
        yield ChoiceField::new('status', 'Status')
            ->setChoices([
                'Entwurf' => 'draft',
                'Veröffentlicht' => 'published',
            ])
            ->renderAsBadges([
                'draft' => 'warning',
                'published' => 'success',
            ])
            ->setColumns(6);
        
        yield BooleanField::new('featured', 'Highlight')
            ->setHelp('Als hervorgehobener Artikel markieren')
            ->setColumns(6);
        
        yield DateTimeField::new('createdAt', 'Erstellt am')
            ->setFormat('dd.MM.yyyy HH:mm')
            ->onlyOnDetail();
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof News) {
            // Auto-set publishedAt when status changes to published
            if ($entityInstance->getStatus() === 'published' && $entityInstance->getPublishedAt() === null) {
                $entityInstance->setPublishedAt(new \DateTime());
            }
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof News) {
            // Auto-set publishedAt when status changes to published
            if ($entityInstance->getStatus() === 'published' && $entityInstance->getPublishedAt() === null) {
                $entityInstance->setPublishedAt(new \DateTime());
            }
        }
        
        parent::updateEntity($entityManager, $entityInstance);
    }
}
