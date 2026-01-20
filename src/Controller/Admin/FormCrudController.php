<?php

namespace App\Controller\Admin;

use App\Entity\Form;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class FormCrudController extends AbstractCrudController
{
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
            ->setHelp('Felder des Formulars');
        
        yield DateTimeField::new('createdAt', 'Erstellt am')
            ->hideOnForm();
    }
}
