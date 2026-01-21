<?php

namespace App\Form;

use App\Entity\FormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Technischer Name',
                'help' => 'Interner Name (z.B. "email", "message")',
                'attr' => ['placeholder' => 'email'],
                'required' => true
            ])
            ->add('label', TextType::class, [
                'label' => 'Anzeige-Label',
                'help' => 'Wird dem Nutzer angezeigt',
                'attr' => ['placeholder' => 'E-Mail-Adresse'],
                'required' => true
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Feldtyp',
                'choices' => [
                    'Text (einzeilig)' => 'text',
                    'E-Mail' => 'email',
                    'Telefon' => 'tel',
                    'Textbereich (mehrzeilig)' => 'textarea',
                    'Auswahl (Select)' => 'select',
                    'Checkbox' => 'checkbox',
                ],
                'required' => true
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'Pflichtfeld',
                'required' => false,
            ])
            ->add('placeholder', TextType::class, [
                'label' => 'Platzhalter',
                'required' => false,
                'attr' => ['placeholder' => 'Optional']
            ])
            ->add('helpText', TextareaType::class, [
                'label' => 'Hilfetext',
                'required' => false,
                'attr' => ['rows' => 2]
            ])
            ->add('sorting', IntegerType::class, [
                'label' => 'Sortierung',
                'data' => 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
