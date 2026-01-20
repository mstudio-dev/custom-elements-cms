<?php

namespace App\Controller\Admin;

use App\Entity\FormSubmission;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class FormSubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FormSubmission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Formular-Einsendung')
            ->setEntityLabelInPlural('Formular-Einsendungen')
            ->setSearchFields(['ipAddress', 'userAgent'])
            ->setDefaultSort(['submittedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('form', 'Formular')
            ->setCrudController(FormCrudController::class);
        
        yield DateTimeField::new('submittedAt', 'Eingereicht am');
        
        yield TextField::new('ipAddress', 'IP-Adresse');
        
        yield TextareaField::new('userAgent', 'User-Agent')
            ->onlyOnDetail()
            ->setMaxLength(100);
        
        yield TextareaField::new('data', 'Daten')
            ->formatValue(function ($value) {
                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            })
            ->setFormTypeOption('attr', ['rows' => 10])
            ->onlyOnDetail();
    }
}
