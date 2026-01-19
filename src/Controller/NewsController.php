<?php

namespace App\Controller;

use App\Repository\MediaRepository;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'news_index')]
    public function index(NewsRepository $newsRepository, MediaRepository $mediaRepository): Response
    {
        $newsList = $newsRepository->findPublished();
        $featured = $newsRepository->findFeatured(3);

        // Load first image for each news item
        $images = [];
        foreach (array_merge($newsList, $featured) as $news) {
            if ($news->getFirstImageId()) {
                $media = $mediaRepository->find($news->getFirstImageId());
                if ($media && $media->isImage()) {
                    $images[$news->getId()] = $media;
                }
            }
        }

        return $this->render('news/index.html.twig', [
            'news_list' => $newsList,
            'featured' => $featured,
            'images' => $images,
        ]);
    }

    #[Route('/news/{slug}', name: 'news_show')]
    public function show(string $slug, NewsRepository $newsRepository, MediaRepository $mediaRepository): Response
    {
        $news = $newsRepository->findOnePublishedBySlug($slug);

        if (!$news) {
            throw $this->createNotFoundException('News nicht gefunden');
        }

        // Load media entities for images
        $images = [];
        if ($news->getImageIds()) {
            foreach ($news->getImageIds() as $imageId) {
                $media = $mediaRepository->find($imageId);
                if ($media && $media->isImage()) {
                    $images[] = $media;
                }
            }
        }

        return $this->render('news/show.html.twig', [
            'news' => $news,
            'images' => $images,
        ]);
    }
}
