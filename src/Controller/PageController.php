<?php

namespace App\Controller;

use App\Repository\ElementRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ElementRepository $elementRepository, PageRepository $pageRepository, Environment $twig): Response
    {
        $pages = $pageRepository->findBy(['published' => true], ['title' => 'ASC']);
        
        $elements = $elementRepository->findBy(
            ['published' => true, 'page' => null],
            ['sorting' => 'ASC']
        );

        // Elemente mit gerendertem Template vorbereiten
        $renderedElements = [];
        foreach ($elements as $element) {
            $template = $element->getElementType()->getTemplate();
            if ($template) {
                $renderedHtml = $twig->render('element_types/' . $template, ['data' => $element->getData()]);
                $renderedElements[] = [
                    'type' => $element->getElementType()->getName(),
                    'html' => $renderedHtml
                ];
            }
        }

        $response = $this->render('page/index.html.twig', [
            'elements' => $renderedElements,
            'pages' => $pages,
        ]);
        
        // Cache for 1 hour
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        return $response;
    }

    #[Route('/{slug}', name: 'page_show', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(string $slug, PageRepository $pageRepository, Environment $twig): Response
    {
        $pages = $pageRepository->findBy(['published' => true], ['title' => 'ASC']);
        $page = $pageRepository->findOneBy(['slug' => $slug, 'published' => true]);

        if (!$page) {
            throw $this->createNotFoundException('Seite nicht gefunden');
        }

        // Elemente der Seite mit gerendertem Template vorbereiten
        $renderedElements = [];
        foreach ($page->getElements() as $element) {
            if (!$element->isPublished()) {
                continue;
            }
            
            $template = $element->getElementType()->getTemplate();
            if ($template) {
                $renderedHtml = $twig->render('element_types/' . $template, ['data' => $element->getData()]);
                $renderedElements[] = [
                    'type' => $element->getElementType()->getName(),
                    'html' => $renderedHtml
                ];
            }
        }

        $response = $this->render('page/show.html.twig', [
            'page' => $page,
            'elements' => $renderedElements,
            'pages' => $pages,
        ]);
        
        // Cache for 1 hour
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        return $response;
    }
}