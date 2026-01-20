<?php

namespace App\Controller\Admin;

use App\Entity\ElementType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class ElementTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ElementType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // Scan template directory for available templates
        $templateDir = $this->getParameter('kernel.project_dir') . '/templates/element_types';
        $templates = [];
        
        if (is_dir($templateDir)) {
            $files = scandir($templateDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'twig') {
                    $templates[$file] = $file;
                }
            }
        }
        
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Technischer Name'),
            TextField::new('label', 'Anzeigename'),
            TextareaField::new('fieldsJson', 'Felder (JSON)')
                ->setHelp('JSON-Definition der Formularfelder')
                ->setFormTypeOption('attr', [
                    'rows' => 20,
                    'style' => 'font-family: monospace; font-size: 14px;'
                ]),
            ChoiceField::new('template', 'Template-Datei')
                ->setChoices($templates)
                ->setHelp('Wähle eine Template-Datei aus templates/element_types/')
                ->renderAsNativeWidget(),
        ];
    }
}