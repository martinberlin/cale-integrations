<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend")
 */
class BackendController extends AbstractController
{
    /**
     * @Route("/", name="b_home")
     */
    public function home()
    {
        return $this->render(
            'backend/admin-home.html.twig'
        );
    }
}