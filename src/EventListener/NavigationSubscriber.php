<?php

namespace App\EventListener;

use App\Repository\PageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class NavigationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private Environment $twig
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Add pages to all Twig templates
        $pages = $this->pageRepository->findBy([], ['title' => 'ASC']);
        $this->twig->addGlobal('pages', $pages);
    }
}
