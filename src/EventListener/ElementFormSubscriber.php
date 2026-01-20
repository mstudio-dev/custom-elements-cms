<?php

namespace App\EventListener;

use App\Entity\Element;
use App\Repository\MediaRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ElementFormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'beforePersist',
            BeforeEntityUpdatedEvent::class => 'beforeUpdate',
        ];
    }

    public function beforePersist(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof Element) {
            return;
        }

        $this->processElementData($entity);
    }

    public function beforeUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof Element) {
            return;
        }

        $this->processElementData($entity);
    }

    private function processElementData(Element $element): void
    {
        $elementType = $element->getElementType();
        if (!$elementType) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $fieldDefinitions = $elementType->getFields();
        $form = $request->request->all()['Element'] ?? [];
        
        $data = [];
        foreach ($fieldDefinitions as $field) {
            $fieldName = 'data_' . $field['name'];
            if (isset($form[$fieldName])) {
                $value = $form[$fieldName];
                
                // Bei Media-Feldern die ID als Integer speichern
                if (($field['type'] === 'image' || $field['type'] === 'media') && $value) {
                    $data[$field['name']] = (int) $value;
                } elseif ($field['type'] === 'checkbox' || $field['type'] === 'boolean') {
                    $data[$field['name']] = (bool) $value;
                } else {
                    $data[$field['name']] = $value;
                }
            }
        }
        
        $element->setData($data);
    }
}
