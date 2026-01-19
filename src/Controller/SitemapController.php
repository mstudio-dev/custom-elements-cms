<?php

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(PageRepository $pageRepository, NewsRepository $newsRepository): Response
    {
        $pages = $pageRepository->findBy(['published' => true], ['createdAt' => 'DESC']);
        $news = $newsRepository->findBy(['status' => 'published'], ['publishedAt' => 'DESC']);

        $urls = [];

        // Homepage
        $urls[] = [
            'loc' => $this->generateUrl('page_show', ['slug' => ''], [], true),
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        // Pages
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $this->generateUrl('page_show', ['slug' => $page->getSlug()], [], true),
                'lastmod' => $page->getCreatedAt()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // News Index
        $urls[] = [
            'loc' => $this->generateUrl('news_index', [], [], true),
            'lastmod' => $news[0]?->getPublishedAt()?->format('Y-m-d') ?? date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ];

        // News Articles
        foreach ($news as $article) {
            $urls[] = [
                'loc' => $this->generateUrl('news_show', ['slug' => $article->getSlug()], [], true),
                'lastmod' => $article->getPublishedAt()?->format('Y-m-d') ?? $article->getCreatedAt()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ];
        }

        $response = $this->render('sitemap/index.xml.twig', [
            'urls' => $urls
        ]);

        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}
