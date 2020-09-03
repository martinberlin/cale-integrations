<?php
namespace App\Controller\Render;

use App\Entity\Display;
use App\Entity\TemplatePartial;
use App\Entity\User;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiGalleryImageRepository;
use App\Repository\UserApiRepository;
use App\Service\SimpleCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to render internal HTML contents
 * @Route("/render")
 */
class GRenderController extends AbstractController
{
    private function convertDateTime($unixTime, $hourFormat)
    {
        $dt = new \DateTime("@$unixTime");
        return $dt->format($hourFormat);
    }

    /**
     * render_gallery is internally called
     * @Route("/gallery", name="render_gallery")
     */
    public function render_gallery(TemplatePartial $partial, IntegrationApiRepository $intApiRepository, UserApiRepository $userApiRepository, UserApiGalleryImageRepository $imageRepository)
    {
        $screen = $partial->getScreen();
        $user = $screen->getUser();
        $imageMaxWidth = ($screen->getDisplay() instanceof Display) ? $screen->getDisplay()->getWidth() : 2000;
        $api = $partial->getIntegrationApi();

        // Are we in a Symfony authenticated context or is the screenshot tool calling
        $isImageCall = false;

        if ($this->getUser() instanceof User === false) {
            $isImageCall = true;
        }
        // Retrieve next image
        $image = $imageRepository->getImageNext($user, $api, $isImageCall);
        $imagePublicPath = $this->getParameter('screen_images_directory') . '/' . $user->getId().'/'.$api->getId();
        $imagePath = $imagePublicPath.'/'.$image->getImageId().'.'.$image->getExtension();

        $html = '<figure class="figure">';
        $float = ($api->getImagePosition()==='center') ? "mx-auto d-block center-img" : "float-{$api->getImagePosition()}";
        $imageHtml = '<img src="'.$imagePath.'" class="figure-img img-fluid '.$float.'" style="max-width:'.$imageMaxWidth.'px">';
        $html .= $imageHtml;
        $isImageCallRender = $isImageCall ? '1':'0';
        $html .= ($image->getCaption()) ? '<figcaption class="figure-caption">'.$isImageCallRender.$image->getCaption().'</figcaption>' : '';
        $html .= '</figure>';
        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }
}