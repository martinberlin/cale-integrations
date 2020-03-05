<?php
namespace App\Controller;

use App\Repository\ApiRepository;
use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HomeController
 * @package App\Controller
 * This Routes are not working with annotations so are defined in routes.yaml
 */
class HomeController extends AbstractController
{
    // This Routes are defined in routes.yaml
    public function index(Request $request)
    {

        return $this->render(
            $request->getLocale().'/www-index.html.twig'
        );
        //return $this->redirectToRoute('register', [], 301);
    }

    public function firmware(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-firmware.html.twig'
        );
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

    public function thanks(Request $request)
    {
        return $this->render(
            $request->getLocale().'/thanks.html.twig'
        );
    }

    public function impressum(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-impressum.html.twig'
        );
    }

    public function privacyPolicy(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-privacy-policy.html.twig'
        );
    }

    public function architecture(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-server-architecture.html.twig'
        );
    }
    public function pricing(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-service-pricing.html.twig'
        );
    }

    public function getStarted(Request $request)
    {
        return $this->render(
            $request->getLocale().'/www-get-started.html.twig'
        );
    }
}