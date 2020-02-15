<?php
namespace App\Controller;

use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend/screen")
 */
class BackendScreenController extends AbstractController
{
    /**
     * @Route("/", name="b_screens")
     */
    public function home()
    {
        return $this->render(
            'backend/admin-screen.html.twig',
            [
                'title' => 'My screens'
            ]
        );
    }

}