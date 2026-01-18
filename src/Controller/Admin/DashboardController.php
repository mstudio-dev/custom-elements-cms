<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use App\Entity\ElementType;
use App\Entity\Media;
use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
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
        yield MenuItem::linkToCrud('Mediathek', 'fa fa-folder-open', Media::class);
        yield MenuItem::linkToCrud('Element Types', 'fa fa-puzzle-piece', ElementType::class);
        yield MenuItem::linkToCrud('Elements', 'fa fa-list', Element::class);
    }
}