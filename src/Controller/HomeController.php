<?php
namespace App\Controller;

use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->redirectToRoute('register', [], 301);
    }


    /**
     * @Route("/eink-displays", name="displays")
     */
    public function displays(DisplayRepository $displayRepository)
    {
        $displays = $displayRepository->findAll();
        return $this->render(
            'www-display.html.twig',
        [
            'displays' => $displays
        ]
        );
    }

    /**
     * @Route("/built-transparently", name="transparently")
     */
    public function transparently()
    {
        return $this->render(
            'www-built-transparently.html.twig'
        );
    }

    /**
     * @Route("/thanks", name="thanks")
     */
    public function thanks()
    {
        return $this->render(
            'thanks.html.twig'
        );
    }
}