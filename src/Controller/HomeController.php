<?php
namespace App\Controller;

use App\Repository\ApiRepository;
use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class HomeController
 * @package App\Controller
 * This Routes are not working with annotations so are defined in routes.yaml
 */
class HomeController extends AbstractController
{
    public function index(Request $request)
    {

        return $this->render(
            $request->getLocale().'/www-index.html.twig'
        );
        //return $this->redirectToRoute('register', [], 301);
    }

    public function aboutCale(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-about-cale.html.twig'
        );
    }

    public function displays(Request $request, DisplayRepository $displayRepository)
    {
        $displays = $displayRepository->findAll();
        return $this->render(
            $request->getLocale().'/www-display.html.twig',
        [
            'displays' => $displays
        ]
        );
    }

    /**
     * @Route("/apis", name="apis")
     */
    public function apis(Request $request, ApiRepository $apiRepository)
    {
        $apis = $apiRepository->findAll();
        return $this->render(
            $request->getLocale().'/www-apis.html.twig',
            [
                'apis' => $apis
            ]
        );
    }

    public function transparently(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-built-transparently.html.twig'
        );
    }

    /**
     * @Route("/thanks", name="thanks")
     */
    public function thanks(Request $request)
    {
        return $this->render(
            $request->getLocale().'/thanks.html.twig'
        );
    }

    /**
     * @Route("/impressum", name="impressum")
     */
    public function impressum(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-impressum.html.twig'
        );
    }

    /**
     * @Route("/privacy-policy", name="privacy-policy")
     */
    public function privacyPolicy(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-privacy-policy.html.twig'
        );
    }
}