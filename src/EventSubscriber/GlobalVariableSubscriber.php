<?php
namespace App\EventSubscriber;

use App\Repository\SectionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class GlobalVariableSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $sectionRepository;

    public function __construct(Environment $twig, SectionRepository $sectionRepository)
    {
        $this->twig = $twig;
        $this->sectionRepository = $sectionRepository;
    }

    private function addSectionGlobalTwig(){
        $sections = array_reverse($this->sectionRepository->findAll());
        $this->twig->addGlobal('sections', $sections);
    }

    private function homeController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\HomeController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function registerController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\RegisterController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function tokenController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\TokenController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function redacController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\RedacController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function redacPostController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\RedacPostController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function adminSectionController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\AdminSectionController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function adminPostController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\AdminPostController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function adminTagController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\AdminTagController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function adminCommentController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\AdminCommentController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function adminUserController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\AdminUserController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    private function adminController(array $controller, Request $request): bool{
        if(get_class($controller[0]) !== "App\\Controller\\AdminController") return false;
        $this->addSectionGlobalTwig();
        return true;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        if(is_array($controller)){
            $request = $event->getRequest();
            if($this->homeController($controller, $request)) return;
            if($this->registerController($controller, $request)) return;
            if($this->tokenController($controller, $request)) return;
            if($this->redacController($controller, $request)) return;
            if($this->redacPostController($controller, $request)) return;
            if($this->adminSectionController($controller, $request)) return;
            if($this->adminPostController($controller, $request)) return;
            if($this->adminTagController($controller, $request)) return;
            if($this->adminCommentController($controller, $request)) return;
            if($this->adminUserController($controller, $request)) return;
            if($this->adminController($controller, $request)) return;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
