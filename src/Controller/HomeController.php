<?php
namespace App\Controller;

use App\Entity\Display;
use App\Repository\ApiRepository;
use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            $request->getLocale().'/about/www-about-cale.html.twig',
            ['title' => $translator->trans('nav_about')]
        );
    }

    public function aboutEthereum(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/about/ethereum-powered.html.twig',
            ['title' => $translator->trans('nav_ethereum')]
        );
    }

    public function threeDModels(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/about/3d-models.html.twig',
            ['title' => $translator->trans('nav_3d')]
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

    public function firmwareIdf(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-idf.html.twig',
            ['title' => $translator->trans('title_firmware_idf')]
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

    public function serviceEpapersForSale(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/epapers-for-sale.html.twig',
            ['title' => $translator->trans('nav_epapers_for_sale')]
        );
    }

    public function cryptoAdvertising(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/advertising/youhodler.html.twig',
            ['title' => $translator->trans('nav_crypto_adv')]
        );
    }

    public function demoDisplay(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/demos/display.html.twig',
            ['title' => $translator->trans('nav_demo_display')]
        );
    }

    public function demoDigitalArt(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/demos/digital-art.html.twig',
            ['title' => $translator->trans('nav_demo_display')]
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

    public function einkDisplays(Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $displaysList = $displayRepository->findBy(['type' => 'eink'],['width' => 'DESC']);
        $displays = array();
        // Cut only short description
        foreach ($displaysList as $d) {
            $pos = strpos($d->getHtmlDescription(), "<ld>");

            $shortDescription = ($pos === false) ? $d->getHtmlDescription() : substr($d->getHtmlDescription(), 0, $pos);
            if (is_null($shortDescription)) $shortDescription = '';
            $d->setHtmlDescription($shortDescription);
            $displays[] = $d;
        }
        return $this->render(
            $request->getLocale().'/display/www-eink.html.twig',
            [
                'displays' => $displays,
                'title' => $translator->trans('nav_displays')
            ]
        );
    }

    public function einkLanding($brand, $id, Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $display = $displayRepository->findOneBy(['type' => 'eink', 'brand' => $brand, 'id' => $id]);
        if ($display instanceof Display === false) throw new NotFoundHttpException('Display #'.$id.' not found');
        $brand = str_replace('_', ' ', $display->getBrand());
        return $this->render(
            $request->getLocale().'/display/www-eink-landing.html.twig',
            [
                'd' => $display,
                'title' => $translator->trans('epaper_from').' '.$brand.' '.$display->getName()
            ]
        );
    }

    public function candlestickCharts(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/api/www-api-candlesticks.html.twig',
            ['title' => $translator->trans('nav_api_crypto')]
        );
    }

    public function productCinwrite(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/product/cinwrite.html.twig',
            ['title' => $translator->trans('nav_pcb_cinwrite')]
        );
    }

    public function productC3Epaper(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/product/c3-controller-24fpc.html.twig',
            ['title' => $translator->trans('nav_pcb_c3_24')]
        );
    }

    public function goodDisplay(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/news/good-display.html.twig',
            ['title' => $translator->trans('nav_good-display')]
        );
    }
}