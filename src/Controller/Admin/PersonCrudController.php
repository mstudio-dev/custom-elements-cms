<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Person')
            ->setEntityLabelInPlural('Personen')
            ->setDefaultSort(['sorting' => 'ASC', 'lastName' => 'ASC'])
            ->setSearchFields(['firstName', 'lastName', 'position', 'email'])
            ->setPaginatorPageSize(30);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('firstName', 'Vorname')
            ->setRequired(true);
            
        yield TextField::new('lastName', 'Nachname')
            ->setRequired(true);
            
        yield TextField::new('position', 'Position/Funktion')
            ->setRequired(false)
            ->setHelp('z.B. Geschäftsführer, Entwickler, etc.');
            
        yield TelephoneField::new('phone', 'Telefonnummer')
            ->setRequired(false);
            
        yield EmailField::new('email', 'E-Mail-Adresse')
            ->setRequired(false);

        yield IntegerField::new('imageId', 'Bild')
            ->setFormTypeOption('attr', ['min' => 1])
            ->setHelp('Media-ID des Bildes')
            ->setRequired(false);
            
        yield IntegerField::new('sorting', 'Sortierung')
            ->setHelp('Niedrigere Werte werden zuerst angezeigt')
            ->hideOnIndex();
            
        yield BooleanField::new('published', 'Veröffentlicht')
            ->renderAsSwitch(false);
            
        yield DateTimeField::new('createdAt', 'Erstellt am')
            ->hideOnForm();
    }
}
