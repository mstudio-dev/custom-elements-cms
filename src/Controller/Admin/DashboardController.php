<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use App\Entity\ElementType;
use App\Entity\Media;
use App\Entity\News;
use App\Entity\Page;
use App\Entity\User;
use App\Repository\ElementRepository;
use App\Repository\MediaRepository;
use App\Repository\NewsRepository;
use App\Repository\PageRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private PageRepository $pageRepository,
        private NewsRepository $newsRepository,
        private MediaRepository $mediaRepository,
        private ElementRepository $elementRepository,
        private UserRepository $userRepository
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Statistiken sammeln
        $stats = [
            'pages_total' => $this->pageRepository->count([]),
            'pages_published' => $this->pageRepository->count(['published' => true]),
            'news_total' => $this->newsRepository->count([]),
            'news_published' => $this->newsRepository->count(['status' => 'published']),
            'media_total' => $this->mediaRepository->count([]),
            'elements_total' => $this->elementRepository->count([]),
            'users_total' => $this->userRepository->count([]),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Custom Elements CMS');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Seiten', 'fa fa-file-alt', Page::class);
        yield MenuItem::linkToCrud('News', 'fa fa-newspaper', News::class);
        yield MenuItem::linkToCrud('Mediathek', 'fa fa-folder-open', Media::class);
        yield MenuItem::linkToCrud('Element Types', 'fa fa-puzzle-piece', ElementType::class);
        yield MenuItem::linkToCrud('Elements', 'fa fa-list', Element::class);
        
        yield MenuItem::section('Benutzerverwaltung');
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out-alt');
    }
}