<?php

namespace App\Controller\Admin;

use App\Entity\Building;
use App\Entity\College;
use App\Entity\Department;
use App\Entity\Key;
use App\Entity\MemberCategory;
use App\Entity\Person;
use App\Entity\Room;
use App\Entity\Theme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(MemberCategoryCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
        // TODO put some cool charts on this dashboard, or something
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CONNECT')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Back to CONNECT', 'fa fa-rotate-left', 'default');
        yield MenuItem::section('IGB');
        yield MenuItem::linkToCrud('Members', 'fas fa-list', Person::class);
//        yield MenuItem::linkToCrud('Key Affiliations', 'fas fa-list', KeyAffiliation::class);
        yield MenuItem::linkToCrud('Member Categories', 'fas fa-list', MemberCategory::class);
        yield MenuItem::linkToCrud('Keys', 'fas fa-list', Key::class);
        yield MenuItem::linkToCrud('Rooms', 'fas fa-list', Room::class);
        yield MenuItem::linkToCrud('Themes', 'fas fa-list', Theme::class);
        yield MenuItem::section('UIUC');
        yield MenuItem::linkToCrud('Buildings', 'fas fa-list', Building::class);
        yield MenuItem::linkToCrud('Colleges', 'fas fa-list', College::class);
        yield MenuItem::linkToCrud('Departments', 'fas fa-list', Department::class);
    }
}