<?php

namespace App\EventListener;

use App\Entity\Element;
use App\Entity\Media;
use App\Repository\MediaRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ElementFormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MediaRepository $mediaRepository
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

        $fields = $elementType->getFields();
        $request = $element->getData(); // Temporär aus dem Form-Data
        
        // Die Daten sind bereits korrekt vom Form gesetzt
        // Hier könnten wir zusätzliche Validierung oder Transformationen vornehmen
    }
}
