<?php

namespace App\Controller;

use App\Repository\ElementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ElementRepository $elementRepository, Environment $twig): Response
    {
        $elements = $elementRepository->findBy(
            ['published' => true],
            ['sorting' => 'ASC']
        );

        // Elemente mit gerendertem Template vorbereiten
        $renderedElements = [];
        foreach ($elements as $element) {
            $template = $element->getElementType()->getTemplate();
            if ($template) {
                $renderedHtml = $twig->createTemplate($template)->render(['data' => $element->getData()]);
                $renderedElements[] = [
                    'type' => $element->getElementType()->getName(),
                    'html' => $renderedHtml
                ];
            }
        }

        return $this->render('page/index.html.twig', [
            'elements' => $renderedElements,
        ]);
    }
}