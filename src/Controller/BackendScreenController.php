<?php
namespace App\Controller;

use App\Entity\Screen;
use App\Entity\TemplatePartial;
use App\Form\Screen\ScreenOutputType;
use App\Form\Screen\ScreenPartialsType;
use App\Form\Screen\ScreenType;
use App\Repository\ScreenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    /**
     * @Route("/", name="b_screens")
     */
    public function screens(ScreenRepository $screenRepository)
    {
        $screens = $screenRepository->findBy(['user'=>$this->getUser()]);
        return $this->render(
            'backend/admin-screen.html.twig',
            [
                'title' => 'My screens',
                'screens' => $screens,
                'user_max_screens' => $this->getUser()->getMaxScreens()
            ]
        );
    }

    /**
     * @Route("/edit/{uuid?}", name="b_screen_edit")
     */
    public function screenEdit($uuid, Request $request, ScreenRepository $screenRepository,
                               EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        if (is_null($uuid)) {
            $screen = new Screen();
            $screen->setUser($this->getUser());
            $title = $translator->trans('Add new')." screen";
            $screensUsed = $this->getUser()->getScreens()->count();
            if ($screensUsed >= $this->getUser()->getMaxScreens()) {
                $this->addFlash('error', "Sorry but the screen limit is set to maximum $screensUsed screens in your account. Please contact us if you want to update this limit");
                return $this->redirectToRoute('b_screens');
            }

        } else {
            $screen = $screenRepository->find($uuid);
            $title = $translator->trans('Edit').' screen "'.$screen->getName().'"';
        }

        $form = $this->createForm(ScreenType::class, $screen, ['templates' => $this->getParameter('screen_templates')]);
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
                $this->addFlash('success', "Screen $uuid ".$translator->trans('saved'));
                return $this->redirectToRoute('b_screens');
            }
        }

            return $this->render(
            'backend/screen/screen-edit.html.twig',
            [
                'title' => $title,
                'form' => $form->createView(),
                'uuid' => $uuid
            ]
        );
    }

    /**
     * @Route("/partials/{uuid?}", name="b_screen_partials")
     */
    public function screenPartialsEdit($uuid, Request $request, ScreenRepository $screenRepository,
                                       EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
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
                'screen' => $screen
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
                'title' => $title,
                'form' => $form->createView(),
                'uuid'     => $uuid,
                'display' => $display,
                'template_twig' => str_replace('.html.twig','',$screen->getTemplateTwig())
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

    private function bmpUrl($htmlUrl, Screen $screen) {
        $extraParams = [
            'u' => $htmlUrl,
            'w' => $screen->getDisplay()->getWidth(),
            'h' => $screen->getDisplay()->getHeight(),
            'b' => $screen->getOutBrightness(),
            'd' => $screen->getOutBitDepth(),
            'c' => $screen->getOutCompressed(),
            'cache' => $screen->getOutCacheSeconds()
        ];
        $url = $_ENV['SCREENSHOT_TOOL'].'?'.http_build_query($extraParams);
        return $url;
    }

    /**
     * @Route("/render/{uuid?}", name="b_screen_render")
     */
    public function screenRender($uuid, Request $request, ScreenRepository $screenRepository,
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
        $partials = $screen->getPartials();
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
        $bmpUrl = $this->bmpUrl($htmlUrl, $screen);

        $renderParams = [
            'uuid' => $uuid,
            'template' => '/screen-templates/'.$template,
            'screen_public' => $screen->isPublic(),
            'screen_hits'   => $screen->getHits(),
            'screen_bearer' => $screen->getOutBearer(),
            'screen_display' => $screen->getDisplay(),
            'form' => $form->createView(),
            'html_url' => $htmlUrl,
            'bmp_url' => $bmpUrl
        ];
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

                $renderParams[$p->getPlaceholder()] = [
                    'content' => $htmlPerColumn[$p->getPlaceholder()]
                ];
            }
        }

        return $this->render(
            'backend/screen/screen-render.html.twig',
            $renderParams);
    }
}