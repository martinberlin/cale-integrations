<?php
namespace App\Controller;

use App\Entity\Display;
use App\Entity\Screen;
use App\Entity\TemplatePartial;
use App\Form\Screen\ScreenOutputType;
use App\Form\Screen\ScreenPartialsType;
use App\Form\Screen\ScreenType;
use App\Repository\ScreenRepository;
use App\Repository\TemplatePartialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/backend/screen")
 */
class BackendScreenController extends AbstractController
{
    private $menu = "screen";

    /**
     * @Route("/", name="b_screens")
     */
    public function screens(Request $request, ScreenRepository $screenRepository, BackendController $backendController)
    {
        $screens = $screenRepository->findBy(['user'=>$this->getUser()]);
        return $this->render(
            'backend/admin-screen.html.twig',
            [
                'title' => 'My screens',
                'screens' => $screens,
                'user_max_screens' => $this->getUser()->getMaxScreens(),
                'isMobile' => $backendController->isMobile($request),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/edit/{uuid?}", name="b_screen_edit")
     */
    public function screenEdit($uuid, Request $request, ScreenRepository $screenRepository,
                               EntityManagerInterface $entityManager, TranslatorInterface $translator, BackendController $backendController)
    {
        $isNewScreen = false;
        if (is_null($uuid)) {
            $screen = new Screen();
            $screen->setUser($this->getUser());
            $title = $translator->trans('Add new')." screen";
            $screensUsed = $this->getUser()->getScreens()->count();
            if ($screensUsed >= $this->getUser()->getMaxScreens()) {
                $this->addFlash('error', "Sorry but the screen limit is set to maximum $screensUsed screens in your account. Please contact us if you want to update this limit");
                return $this->redirectToRoute('b_screens');
            }
            $isNewScreen = true;
        } else {
            $screen = $screenRepository->find($uuid);
            $title = $translator->trans('Edit').' screen "'.$screen->getName().'"';
        }

        $form = $this->createForm(ScreenType::class, $screen, [
            'templates' => $this->getParameter('screen_templates'),
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);
        $error = '';

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $entityManager->persist($screen);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==='') {
                $this->addFlash('success', "Screen $uuid: ".$screen->getName()." ".$translator->trans('saved'));
                return $this->redirectToRoute('b_screens');
            }
        }

        $imageType = ($screen->getDisplay() instanceof Display && $screen->getDisplay()->getType() === 'eink') ?
            'bmp' : 'jpg';

            return $this->render(
            'backend/screen/screen-edit.html.twig',
            [
                'title' => $title,
                'form' => $form->createView(),
                'uuid' => $uuid,
                'isMobile' => $backendController->isMobile($request),
                'image_url'  => ($screen->getDisplay() instanceof Display) ?
                    $this->imageUrlGenerator($screen->isOutSsl(), $imageType, $this->getUser()->getName(), $screen->getId()): '',
                'is_new' => $isNewScreen,
                'menu' => $this->menu
            ]
        );
    }

    private function thumbnailUrlGenerator($username, $screenId) {
        $schema = 'http://';
        $url = $schema.$_ENV['SCREENSHOT_TOOL'].'/bmp/'.$username.'/'.$screenId.'?thumbnail=1';
        return $url;
    }

    /**
     * @Route("/partials/{uuid?}", name="b_screen_partials")
     */
    public function screenPartialsEdit($uuid, Request $request, ScreenRepository $screenRepository,
                                       EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $templateContents = $this->getParameter('screen_templates_contents');
        $screen = $screenRepository->find($uuid);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        if ($screen->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("$uuid is not your screen");
        }

        $title = $translator->trans('screen_partials_title').' "'.$screen->getName().'"';

        $form = $this->createForm(ScreenPartialsType::class, $screen,
            [
                'screen' => $screen,
                'screen_template' => $screen->getTemplateTwig(),
                'template_placeholders' => $templateContents
            ]);
        $form->handleRequest($request);
        $error = '';

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($screen->getPartials() as $partial) {
                $partial->setScreen($screen);
            }
            try {
                $entityManager->persist($screen);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==='') {
                $this->addFlash('success', "Partials for screen $uuid saved");
            }
        }
        $display = $screen->getDisplay();
        return $this->render(
            'backend/screen/screen-partials.html.twig',
            [
                'title'   => $title,
                'form'    => $form->createView(),
                'uuid'    => $uuid,
                'display' => $display,
                'screen_public' => $screen->isPublic(),
                'template_twig' => str_replace('.html.twig','',$screen->getTemplateTwig()),
                'thumbnail_src' => $this->thumbnailUrlGenerator($this->getUser()->getName(), $uuid),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/delete/{uuid?}", name="b_screen_delete")
     */
    public function screenDelete($uuid, Request $request, ScreenRepository $screenRepository, EntityManagerInterface $entityManager)
    {
        $screen = $screenRepository->find($uuid);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        if ($screen->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("$uuid is not your screen");
        }
        $error = '';
        try {
            $entityManager->remove($screen);
            $entityManager->flush();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->addFlash('error', $error);
        }
        if ($error==='') {
            $extraMessage = (rand(0,2)===1) ? "Keeping your dashboard clean?" : "";
            $this->addFlash('success', "Deleted screen $uuid. $extraMessage");
        }
        return $this->redirectToRoute('b_screens');
    }

    /**
     * @param $username
     * @param $screenId
     * @return string
     */
    private function imageUrlGenerator($isSsl, $responseType, $username, $screenId) {
        $schema = ($isSsl) ? 'https://' : 'http://';
        $url = $schema.$_ENV['SCREENSHOT_TOOL'].'/'.$responseType.'/'.$username.'/'.$screenId;
        return $url;
    }

    /**
     * @Route("/render/{uuid?}", name="b_screen_render")
     */
    public function screenRender($uuid, Request $request, ScreenRepository $screenRepository, TemplatePartialRepository $partialRepository,
                                 EntityManagerInterface $em)
    {
        $screen = $screenRepository->find($uuid);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        if ($screen->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("$uuid is not your screen");
        }

        $form = $this->createForm(ScreenOutputType::class, $screen);
        $template = $screen->getTemplateTwig();
        $partials = $partialRepository->findBy(['screen' => $screen], ['sortPos' => 'ASC']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($screen);
                $em->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if (!isset($error)) {
                $this->addFlash('success', "Screen settings saved");
            }
        }
        $screenParams = [
            'username' => $this->getUser()->getName(),
            'uuid'     => $uuid
        ];
        $htmlUrl = $this->generateUrl('public_screen_render', $screenParams, UrlGeneratorInterface::ABSOLUTE_URL);
        // Display can be optionally left unassigned
        $isDisplayAssigned = $screen->getDisplay() instanceof Display;
        $imageType = ($isDisplayAssigned && $screen->getDisplay()->getType()==='eink') ?'bmp':'jpg';
        $imageUrl = ($isDisplayAssigned) ?
            $this->imageUrlGenerator($screen->isOutSsl(), $imageType, $this->getUser()->getName(), $screen->getId()): '';

        $renderParams = [
            'uuid' => $uuid,
            'template' => '/screen-templates/'.$template,
            'screen_public' => $screen->isPublic(),
            'screen_hits'   => $screen->getHits(),
            'screen_bearer' => $screen->getOutBearer(),
            'screen_display' => $screen->getDisplay(),
            'screen_image_type' => strtoupper($imageType),
            'form' => $form->createView(),
            'html_url' => $htmlUrl,
            'image_url' => $imageUrl,
            'menu' => $this->menu
        ];
        $htmlPerColumn['Header'] = '';
        $htmlPerColumn['Column_1st'] = '';
        $htmlPerColumn['Column_2nd'] = '';
        $htmlPerColumn['Column_3rd'] = '';
        foreach ($partials as $p) {
            if ($p instanceof TemplatePartial) {
                $partialHtml = $this->forward($p->getIntegrationApi()->getUserApi()->getApi()->getJsonRoute(),
                    [
                        'partial' => $p
                    ]);
                $htmlPerColumn[$p->getPlaceholder()] .= $partialHtml->getContent();

                $renderParams[$p->getPlaceholder()] = $htmlPerColumn[$p->getPlaceholder()];
            }
        }

        return $this->render(
            'backend/screen/screen-render.html.twig',
            $renderParams);
    }

    /**
     * @Route("/render_image/{uuid}/{isThumbnail}", name="b_render_image")
     */
    public function screenRenderImage($uuid, $isThumbnail, ScreenRepository $screenRepository) {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }
        $screen = $screenRepository->find($uuid);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        if (!$this->isGranted('ROLE_ADMIN') && $screen->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("$uuid is not your screen");
        }
        $bmpUrl = ($screen->getDisplay() instanceof Display) ?
            $this->imageUrlGenerator($screen->isOutSsl(), 'bmp', $screen->getUser()->getName(), $screen->getId()): '';
        if ($isThumbnail) {
            $bmpUrl.= '?thumbnail=1';
        }

        // HttpClient
        $client = HttpClient::create([
            // HTTP Bearer authentication (also called token authentication)
            'auth_bearer' => $screen->getOutBearer()
        ]);

        $cliResponse = $client->request('GET', $bmpUrl, $options);

        $response = new Response();
        $cliHeaders = $cliResponse->getHeaders();
        $response->headers->set('Content-type', reset($cliHeaders['content-type']));
        $response->setContent($cliResponse->getContent());
        return $response;
    }

}