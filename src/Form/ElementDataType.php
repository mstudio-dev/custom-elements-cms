<?php

namespace App\Form;

use App\Entity\Element;
use App\Entity\Media;
use App\Repository\MediaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $element = $event->getData();
            $form = $event->getForm()->getParent();

            if (!$form || !$element instanceof Element || !$element->getElementType()) {
                return;
            }

            $fieldDefinitions = $element->getElementType()->getFieldDefinitions();
            $data = $element->getData();

            foreach ($fieldDefinitions as $field) {
                $fieldName = 'data_' . $field['name'];
                $fieldType = $field['type'];
                $label = $field['label'] ?? ucfirst($field['name']);
                $required = $field['required'] ?? false;

                if ($form->has($fieldName)) {
                    continue;
                }

                switch ($fieldType) {
                    case 'image':
                    case 'media':
                        $form->add($fieldName, EntityType::class, [
                            'class' => Media::class,
                            'choice_label' => function (Media $media) {
                                return $media->getFilename() . ' (' . $media->getTitle() . ')';
                            },
                            'label' => $label,
                            'required' => $required,
                            'placeholder' => '-- Bitte wählen --',
                            'mapped' => false,
                            'data' => isset($data[$field['name']]) ? $this->mediaRepository->find($data[$field['name']]) : null,
                        ]);
                        break;

                    case 'textarea':
                        $form->add($fieldName, TextareaType::class, [
                            'label' => $label,
                            'required' => $required,
                            'mapped' => false,
                            'data' => $data[$field['name']] ?? '',
                            'attr' => ['rows' => 8],
                        ]);
                        break;

                    case 'text':
                    default:
                        $form->add($fieldName, TextType::class, [
                            'label' => $label,
                            'required' => $required,
                            'mapped' => false,
                            'data' => $data[$field['name']] ?? '',
                        ]);
                        break;
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Element::class,
        ]);
    }
}
