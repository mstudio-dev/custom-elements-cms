<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use App\Entity\Media;
use App\Form\ElementDataType;
use App\Repository\MediaRepository;
use App\Repository\FormRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormEvents;

class ElementCrudController extends AbstractCrudController
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private FormRepository $formRepository,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}
    
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

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplizieren', 'fa fa-copy')
            ->linkToCrudAction('duplicateElement')
            ->setCssClass('btn btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate);
    }

    public function duplicateElement(): RedirectResponse
    {
        $originalElement = $this->getContext()->getEntity()->getInstance();
        
        $newElement = new Element();
        $newElement->setElementType($originalElement->getElementType());
        $newElement->setPage($originalElement->getPage());
        $newElement->setData($originalElement->getData());
        $newElement->setSorting($originalElement->getSorting() + 1);
        $newElement->setActive($originalElement->isActive());
        
        $this->entityManager->persist($newElement);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Element wurde erfolgreich dupliziert.');
        
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($newElement->getId())
            ->generateUrl();
        
        return new RedirectResponse($url);
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
        
        // JSON Textarea als Fallback (wird ausgeblendet wenn dynamische Felder verfügbar)
        $showJsonFallback = true;
        
        // Dynamische Felder basierend auf ElementType
        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            $entity = $this->getContext()->getEntity()->getInstance();
            if ($entity instanceof Element && $entity->getElementType()) {
                $showJsonFallback = false;
                $fieldDefinitions = $entity->getElementType()->getFields();
                $data = $entity->getData();
                
                foreach ($fieldDefinitions as $field) {
                    $fieldName = 'data_' . $field['name'];
                    $fieldLabel = $field['label'] ?? $field['name'];
                    $fieldType = $field['type'] ?? 'text';
                    
                    switch ($fieldType) {
                        case 'image':
                        case 'media':
                            $mediaId = $data[$field['name']] ?? null;
                            $allMedia = $this->mediaRepository->findBy(['type' => 'file'], ['name' => 'ASC']);
                            
                            $choices = [];
                            foreach ($allMedia as $media) {
                                $label = $media->getFilename();
                                if ($media->getParent()) {
                                    $label = $media->getParent()->getName() . '/' . $label;
                                }
                                $choices[$label] = $media->getId();
                            }
                            
                            yield ChoiceField::new($fieldName, $fieldLabel)
                                ->setChoices($choices)
                                ->setFormTypeOption('mapped', false)
                                ->setFormTypeOption('data', $mediaId)
                                ->setFormTypeOption('placeholder', '- Keine Auswahl -')
                                ->setRequired(false);
                            break;
                            
                        case 'richtext':
                            yield TextareaField::new($fieldName, $fieldLabel)
                                ->setFormTypeOption('mapped', false)
                                ->setFormTypeOption('data', $data[$field['name']] ?? '')
                                ->setFormTypeOption('attr', ['data-richtext' => 'true'])
                                ->setRequired(false);
                            break;
                            
                        case 'textarea':
                            yield TextareaField::new($fieldName, $fieldLabel)
                                ->setFormTypeOption('mapped', false)
                                ->setFormTypeOption('data', $data[$field['name']] ?? '')
                                ->setRequired(false);
                            break;
                            
                        case 'checkbox':
                        case 'boolean':
                            yield BooleanField::new($fieldName, $fieldLabel)
                                ->setFormTypeOption('mapped', false)
                                ->setFormTypeOption('data', $data[$field['name']] ?? false)
                                ->setRequired(false);
                            break;
                            
                        case 'choice':
                            $choices = [];
                            if (isset($field['choices']) && is_array($field['choices'])) {
                                // Format: {"value": "Label"} oder einfaches Array
                                $choices = $field['choices'];
                            }
                            
                            // Spezialfall: form_id -> Dropdown mit verfügbaren Formularen
                            if ($field['name'] === 'form_id') {
                                $forms = $this->formRepository->findAll();
                                $choices = [];
                                foreach ($forms as $form) {
                                    $choices[$form->getName()] = $form->getId();
                                }
                            }
                            
                            yield ChoiceField::new($fieldName, $fieldLabel)
                                ->setChoices($choices)
                                ->setFormTypeOption('mapped', false)
                                ->setFormTypeOption('data', $data[$field['name']] ?? null)
                                ->setFormTypeOption('placeholder', '- Bitte wählen -')
                                ->setRequired($field['required'] ?? false);
                            break;
                            
                        case 'text':
                        default:
                            yield TextField::new($fieldName, $fieldLabel)
                                ->setFormTypeOption('mapped', false)
                                ->setFormTypeOption('data', $data[$field['name']] ?? '')
                                ->setRequired(false);
                            break;
                    }
                }
            }
        }
        
        // Fallback JSON-Textarea wenn kein ElementType vorhanden
        if ($showJsonFallback && ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW)) {
            yield TextareaField::new('dataJson', 'Daten (JSON)')
                ->setHelp('JSON-Editor - wähle zuerst einen Element-Typ für individuelle Felder');
        }
        
        // Kompakte JSON-Ansicht in der Übersicht
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