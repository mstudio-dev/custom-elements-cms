<?php

namespace App\Twig;

use App\Repository\MediaRepository;
use App\Repository\NewsRepository;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class RepositoryExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NewsRepository $newsRepository,
        private MediaRepository $mediaRepository,
        private PageRepository $pageRepository
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'pages' => $this->pageRepository->findBy([], ['title' => 'ASC']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_published_news', [$this, 'getPublishedNews']),
            new TwigFunction('get_featured_news', [$this, 'getFeaturedNews']),
            new TwigFunction('get_media', [$this, 'getMedia']),
        ];
    }

    public function getPublishedNews(?int $limit = null): array
    {
        return $this->newsRepository->findPublished($limit);
    }

    public function getFeaturedNews(?int $limit = 3): array
    {
        return $this->newsRepository->findFeatured($limit);
    }

    public function getMedia(int $id)
    {
        return $this->mediaRepository->find($id);
    }
}
