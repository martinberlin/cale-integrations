<?php
namespace App\Controller\Render;

use App\Entity\Screen;
use App\Form\Screen\ScreenPartialsType;
use App\Form\Screen\ScreenType;
use App\Repository\ScreenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/render")
 */
class PublicScreenRenderController extends AbstractController
{
    /**
     * @Route("/{uuid?}", name="public_screen_render")
     */
    public function publicScreenRender($uuid, Request $request, ScreenRepository $screenRepository)
    {
        // For this controller action, the profiler is disabled
        if ($this->container->has('profiler')) {
            $profiler = $this->get('profiler');
            $profiler->disable();
        }

        $screen = $screenRepository->find($uuid);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        $template = $screen->getTemplateTwig();
        $partials = $screen->getPartials();

        $renderParams = [
            'template' => '/screen-templates/'.$template
        ];
        $htmlPerColumn['Column_1st'] = '';
        $htmlPerColumn['Column_2nd'] = '';
        $htmlPerColumn['Column_3rd'] = '';
        foreach ($partials as $p) {
            $partialHtml = $this->forward($p->getIntegrationApi()->getUserApi()->getApi()->getJsonRoute(),
                [
                'partial' => $p
                ]);
            $htmlPerColumn[$p->getPlaceholder()] .= $partialHtml->getContent();

            $renderParams[$p->getPlaceholder()] = [
                'content'        => $htmlPerColumn[$p->getPlaceholder()]
            ];
        }
        return $this->render(
            'public/screen-render.html.twig',
            $renderParams);
    }
}