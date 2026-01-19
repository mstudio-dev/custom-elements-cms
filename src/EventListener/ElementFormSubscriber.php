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
        // In EasyAdmin 4.x ist getAsDto() nicht mehr verfügbar
        // Die dynamischen Felder werden über ElementDataType gehandhabt
        // EventSubscriber vorerst deaktiviert
        return;
    }
}
