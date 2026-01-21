<?php

namespace App\Controller\Admin;

use App\Entity\Form;
use App\Entity\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FormCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }
    public static function getEntityFqcn(): string
    {
        return Form::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Formular')
            ->setEntityLabelInPlural('Formulare')
            ->setSearchFields(['name', 'recipient', 'subject'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new('duplicate', 'Duplizieren', 'fa fa-copy')
            ->linkToCrudAction('duplicateForm');

        return $actions
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $duplicate);
    }

    public function duplicateForm(): RedirectResponse
    {
        $originalForm = $this->getContext()->getEntity()->getInstance();
        
        $newForm = new Form();
        $newForm->setName($originalForm->getName() . ' (Kopie)');
        $newForm->setRecipient($originalForm->getRecipient());
        $newForm->setSubject($originalForm->getSubject());
        $newForm->setSuccessMessage($originalForm->getSuccessMessage());
        $newForm->setStoreSubmissions($originalForm->isStoreSubmissions());
        
        // Felder kopieren
        foreach ($originalForm->getFields() as $originalField) {
            $newField = new FormField();
            $newField->setName($originalField->getName());
            $newField->setLabel($originalField->getLabel());
            $newField->setType($originalField->getType());
            $newField->setRequired($originalField->isRequired());
            $newField->setPlaceholder($originalField->getPlaceholder());
            $newField->setOptions($originalField->getOptions());
            $newField->setSorting($originalField->getSorting());
            $newField->setHelpText($originalField->getHelpText());
            $newField->setForm($newForm);
            
            $this->entityManager->persist($newField);
        }
        
        $this->entityManager->persist($newForm);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Formular wurde erfolgreich dupliziert.');
        
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($newForm->getId())
            ->generateUrl();
        
        return new RedirectResponse($url);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield TextField::new('name', 'Formular-Name')
            ->setHelp('Interner Name zur Identifikation');
        
        yield TextField::new('recipient', 'Empfänger E-Mail')
            ->setHelp('E-Mail-Adresse für Formular-Submissions');
        
        yield TextField::new('subject', 'E-Mail Betreff')
            ->setHelp('Betreff der Benachrichtigungs-E-Mail');
        
        yield TextareaField::new('successMessage', 'Erfolgsmeldung')
            ->setHelp('Nachricht die dem Nutzer nach erfolgreicher Übermittlung angezeigt wird')
            ->setFormTypeOption('attr', ['rows' => 3]);
        
        yield BooleanField::new('storeSubmissions', 'Submissions speichern')
            ->setHelp('Sollen die Formulareingaben in der Datenbank gespeichert werden?');
        
        yield CollectionField::new('fields', 'Formularfelder')
            ->onlyOnForms()
            ->setEntryType(\App\Form\FormFieldType::class)
            ->setFormTypeOption('by_reference', false)
            ->setFormTypeOption('allow_add', true)
            ->setFormTypeOption('allow_delete', true)
            ->setFormTypeOption('delete_empty', true)
            ->setHelp('Felder des Formulars');
        
        yield DateTimeField::new('createdAt', 'Erstellt am')
            ->hideOnForm();
    }
}
