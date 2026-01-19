<?php

namespace App\EventListener;

use App\Entity\Element;
use App\Entity\Media;
use App\Form\ElementDataType;
use App\Repository\MediaRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ElementFormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MediaRepository $mediaRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCrudActionEvent::class => 'onBeforeCrudAction',
        ];
    }

    public function onBeforeCrudAction(BeforeCrudActionEvent $event): void
    {
        $context = $event->getAdminContext();
        if (!$context) {
            return;
        }

        $entity = $context->getEntity();
        if (!$entity || $entity->getFqcn() !== Element::class) {
            return;
        }

        $formBuilder = $context->getCrud()->getAsDto()->getFormOptions()['form_builder'] ?? null;
        if (!$formBuilder instanceof FormBuilderInterface) {
            return;
        }

        // Event Listener für dynamische Felder hinzufügen
        $mediaRepository = $this->mediaRepository;
        
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($mediaRepository) {
            $element = $event->getData();
            $form = $event->getForm();

            if (!$element instanceof Element || !$element->getElementType()) {
                return;
            }

            $fieldDefinitions = $element->getElementType()->getFieldDefinitions();
            $data = $element->getData();

            foreach ($fieldDefinitions as $field) {
                $fieldName = 'data_' . $field['name'];
                $fieldType = $field['type'];
                $label = $field['label'] ?? ucfirst($field['name']);
                $required = $field['required'] ?? false;

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
                            'data' => isset($data[$field['name']]) ? $mediaRepository->find($data[$field['name']]) : null,
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
}
