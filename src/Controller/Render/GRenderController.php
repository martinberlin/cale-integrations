<?php
namespace App\Controller\Render;

use App\Entity\Display;
use App\Entity\TemplatePartial;
use App\Entity\User;
use App\Entity\UserApiGalleryImage;
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
        $fColor = ($partial->getInvertedColor()===false) ? $partial->getForegroundColor() : $partial->getBackgroundColor();
        // Are we in a Symfony authenticated context or is the screenshot tool calling
        $isImageCall = false;
        
        if ($this->getUser() instanceof User === false) {
            $isImageCall = true;
        }
        // Retrieve next image
        $image = $imageRepository->getImageNext($user, $api, $isImageCall);
        if ($image instanceof UserApiGalleryImage) {
        $imagePublicPath = $this->getParameter('screen_images_directory') . '/' . $user->getId().'/'.$api->getId();
        $imagePath = $imagePublicPath.'/'.$image->getImageId().'.'.$image->getExtension();
        $textAlignCenterOpen = ($api->getImagePosition()==='center') ? '<center>' : '';
        $textAlignCenterClose = ($api->getImagePosition()==='center') ? '</center>' : '';

        $rowOpen = '<div class="row"><div class="col-md-12 col-sm-12 col-xs-12">'.$textAlignCenterOpen."\n";
        $rowClose = $textAlignCenterClose."\n"."</div></div>";

        $html = $rowOpen;
        $html .= '<img src="'.$imagePath.'" class="figure-img img-fluid" style="max-width:'.$imageMaxWidth.'px;float:'.$api->getImagePosition().'">'.$rowClose;

        $html .= ($image->getCaption()) ?
            $rowOpen.'<span style="margin-left:0.3em;margin-right:0.3em;font-weight:bold;color:'.$fColor.';font-size:10pt;float:'.$api->getImagePosition().'">'.$image->getCaption().'</span>'.$rowClose : '';
        } else {
            $html = "<b>The gallery '{$image}' has no images. Please add one or just remove the content partial from the Screen.</b>";
        }

        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }
}