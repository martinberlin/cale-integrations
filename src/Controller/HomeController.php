<?php
namespace App\Controller;

use App\Repository\ApiRepository;
use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function aboutCale(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-about-cale.html.twig',
            ['title' => $translator->trans('nav_about')]
        );
    }

    public function einkDisplays(Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $displays = $displayRepository->findBy(['type' => 'eink'],['width' => 'DESC']);
        return $this->render(
            $request->getLocale().'/display/www-eink.html.twig',
        [
            'displays' => $displays,
            'title' => $translator->trans('nav_displays')
        ]
        );
    }

    public function tftDisplays(Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $displays = $displayRepository->findBy(['type' => 'tft'],['width' => 'DESC']);
        return $this->render(
            $request->getLocale().'/display/www-tft.html.twig',
            [
                'displays' => $displays,
                'title' => $translator->trans('title_displays_tft')
            ]
        );
    }

    public function apis(Request $request, ApiRepository $apiRepository, TranslatorInterface $translator)
    {
        $apis = $apiRepository->findAll();
        return $this->render(
            $request->getLocale().'/api/www-apis.html.twig',
            [
                'apis' => $apis,
                'title' => $translator->trans('nav_apis')
            ]
        );
    }

    public function transparently(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-built-transparently.html.twig',
            ['title' => $translator->trans('nav_transparently_built')]
        );
    }

    public function thanks(Request $request)
    {
        return $this->render(
            $request->getLocale().'/thanks.html.twig'
        );
    }

    public function impressum(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-impressum.html.twig',
            ['title' => $translator->trans('nav_legal')]
        );
    }

    public function privacyPolicy(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/privacy/www-privacy-policy.html.twig',
            ['title' => $translator->trans('nav_privacy')]
        );
    }

    public function googlePrivacyPolicy(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/privacy/google-privacy-policy.html.twig',
            ['title' => $translator->trans('nav_google_privacy')]
        );
    }

    public function architecture(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/www-server-architecture.html.twig',
            ['title' => $translator->trans('nav_server_architecture')]
        );
    }

    public function pricing(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/www-service-pricing.html.twig',
            ['title' => $translator->trans('nav_pricing')]
        );
    }

    public function faq(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/www-faq.html.twig',
            ['title' => $translator->trans('nav_faq')]
        );
    }
    public function getStarted(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-get-started.html.twig',
            ['title' => $translator->trans('nav_get_started')]
        );
    }

    public function firmware(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware.html.twig',
            ['title' => $translator->trans('title_firmware')]
        );
    }

    public function firmwareTft(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-tft.html.twig',
            ['title' => $translator->trans('title_firmware_tft')]
        );
    }

    public function firmwareT5(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-t5.html.twig',
            ['title' => $translator->trans('title_firmware_t5')]
        );
    }

    public function firmwareBlue(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-blue.html.twig',
            ['title' => $translator->trans('title_firmware_blue')]
        );
    }

    public function apiIcal(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/api/www-api-ical.html.twig',
            ['title' => $translator->trans('title_ical')]
        );
    }

    public function cloudwatch(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/api/www-api-aws-cloudwatch.html.twig',
            ['title' => $translator->trans('title_cloudwatch')]
        );
    }

    public function news(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/news/news.html.twig',
            ['title' => 'News']
        );
    }

    /**
     * Good idea but does not work because of locale lang. switch
     * @deprecated Don't use as is
     * @param Request $request
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function serve(Request $request, $page)
    {
        return $this->render(
            $request->getLocale().'/'.$page.'.html.twig'
        );
    }
}