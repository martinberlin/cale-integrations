<?php
namespace App\Controller\Render;

use App\Entity\Screen;
use App\Entity\User;
use App\Form\Screen\ScreenPartialsType;
use App\Form\Screen\ScreenType;
use App\Repository\ScreenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PublicScreenRenderController extends AbstractController
{
    /**
     * @Route("/{username}/render/{uuid?}", name="public_screen_render")
     */
    public function publicScreenRender($uuid, $username, Request $request, ScreenRepository $screenRepository, UserRepository $userRepository,
                                       LoggerInterface $logger, ?Profiler $profiler)
    {
        // For this controller action if exists (dev) the profiler is disabled
        if (null !== $profiler) {
            $profiler->disable();
        }
        $user = $userRepository->findOneBy(['name' => $username]);
        if (!$user instanceof User) {
            throw $this->createNotFoundException("This user does not exist. Please check the URL");
        }
        try {
            date_default_timezone_set($user->getTimezone());
        } catch (\ErrorException $exception) {
            $logger->info('User '.$user->getId().' set timezone '.$user->getTimezone().' failed.',
                ['caller' =>'TimezoneListener']);
        }

        $screen = $screenRepository->findOneBy(['user' => $user, 'uuid' => $uuid]);
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
                ['partial' => $p]);
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