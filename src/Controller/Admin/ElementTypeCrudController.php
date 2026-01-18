<?php

namespace App\Controller\Admin;

use App\Entity\ElementType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ElementTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ElementType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Technischer Name'),
            TextField::new('label', 'Anzeigename'),
            TextareaField::new('fieldsJson', 'Felder (JSON)')
                ->setHelp('Beispiel: [{"name":"title","type":"text","label":"Titel"}]')
                ->setFormTypeOption('attr', ['rows' => 10]),
            TextareaField::new('template', 'Twig Template')
                ->setHelp('Beispiel: <h2>{{ data.title }}</h2><p>{{ data.content }}</p>')
                ->setFormTypeOption('attr', ['rows' => 10]),
        ];
    }
}