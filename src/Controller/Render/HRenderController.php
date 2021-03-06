<?php
namespace App\Controller\Render;

use App\Entity\TemplatePartial;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use App\Service\SimpleCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to render internal HTML contents
 * @Route("/html")
 */
class HRenderController extends AbstractController
{
    private function convertDateTime($unixTime, $hourFormat)
    {
        $dt = new \DateTime("@$unixTime");
        return $dt->format($hourFormat);
    }

    /**
     * render_html is internally called
     * @Route("/render", name="render_html")
     */
    public function render_html(TemplatePartial $partial, IntegrationApiRepository $intApiRepository, UserApiRepository $userApiRepository)
    {
        $api = $partial->getIntegrationApi();
        $image = $api->getImagePath();
        $html = '';
        $imageHtml = '';
        $closingRow = '';

        if (!is_null($image)) {
            switch ($api->getImageType()) {
                case 'background':
                    $html = '<div class="row" style="background-image:url('.$image.');background-position:'.$api->getImagePosition().';background-repeat:no-repeat">';
                    $closingRow = '</div>';
                    break;

                case 'float':
                    $float = ($api->getImagePosition()==='center') ? "mx-auto d-block center-img" : "float-{$api->getImagePosition()}";
                    $imageHtml = '<img src="'.$image.'" class="rounded '.$float.'">';
                    $html .= $imageHtml;
                    break;
            }
        }

        $html .= $api->getHtml();
        $user = $partial->getScreen()->getUser();
        $dateFormat = $user->getDateFormat();
        $hourFormat = $user->getHourFormat();
        $now = new \DateTime();
        $html = str_replace('{date}', $now->format($dateFormat), $html);
        $html = str_replace('{time}', $now->format($hourFormat), $html);
        $html .= $closingRow;
        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }
}