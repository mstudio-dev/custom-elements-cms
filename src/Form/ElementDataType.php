<?php

namespace App\Form;

use App\Entity\Element;
use App\Entity\Media;
use App\Repository\MediaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementDataType extends AbstractType
{
    public function __construct(
        private MediaRepository $mediaRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Element $element */
        $element = $options['data'];
        
        if (!$element || !$element->getElementType()) {
            return;
        }

        $fieldDefinitions = $element->getElementType()->getFields();
        $data = $element->getData();

        foreach ($fieldDefinitions as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'] ?? 'text';
            $label = $field['label'] ?? ucfirst($fieldName);
            $required = $field['required'] ?? false;

            switch ($fieldType) {
                case 'image':
                case 'media':
                    $builder->add($fieldName, EntityType::class, [
                        'class' => Media::class,
                        'choice_label' => function (Media $media) {
                            return $media->getFilename() . ' (' . $media->getTitle() . ')';
                        },
                        'label' => $label,
                        'required' => $required,
                        'placeholder' => '-- Bitte wählen --',
                        'mapped' => false,
                        'data' => isset($data[$fieldName]) ? $this->mediaRepository->find($data[$fieldName]) : null,
                    ]);
                    break;

                case 'richtext':
                    $builder->add($fieldName, TextareaType::class, [
                        'label' => $label,
                        'required' => $required,
                        'mapped' => false,
                        'data' => $data[$fieldName] ?? '',
                        'attr' => [
                            'data-richtext' => 'true',
                            'rows' => 10
                        ],
                    ]);
                    break;

                case 'textarea':
                    $builder->add($fieldName, TextareaType::class, [
                        'label' => $label,
                        'required' => $required,
                        'mapped' => false,
                        'data' => $data[$fieldName] ?? '',
                        'attr' => ['rows' => 8],
                    ]);
                    break;

                case 'checkbox':
                case 'boolean':
                    $builder->add($fieldName, CheckboxType::class, [
                        'label' => $label,
                        'required' => false,
                        'mapped' => false,
                        'data' => $data[$fieldName] ?? false,
                    ]);
                    break;

                case 'text':
                default:
                    $builder->add($fieldName, TextType::class, [
                        'label' => $label,
                        'required' => $required,
                        'mapped' => false,
                        'data' => $data[$fieldName] ?? '',
                    ]);
                    break;
            }
        }

        // Form-Submit Handler
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($fieldDefinitions) {
            $element = $event->getData();
            $form = $event->getForm();
            
            if (!$element instanceof Element) {
                return;
            }

            $data = [];
            foreach ($fieldDefinitions as $field) {
                $fieldName = $field['name'];
                $fieldType = $field['type'] ?? 'text';
                
                if (!$form->has($fieldName)) {
                    continue;
                }

                $fieldData = $form->get($fieldName)->getData();
                
                // Media-Felder: Nur die ID speichern
                if (($fieldType === 'image' || $fieldType === 'media') && $fieldData instanceof Media) {
                    $data[$fieldName] = $fieldData->getId();
                } elseif ($fieldType === 'richtext') {
                    // Richtext: HTML-Entities dekodieren, damit echtes HTML gespeichert wird
                    $data[$fieldName] = html_entity_decode($fieldData, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } else {
                    $data[$fieldName] = $fieldData;
                }
            }
            
            $element->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Element::class,
        ]);
    }
}
