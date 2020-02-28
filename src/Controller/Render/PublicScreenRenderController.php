<?php
namespace App\Controller\Render;

use App\Entity\Screen;
use App\Entity\TemplatePartial;
use App\Entity\User;
use App\Repository\ScreenRepository;
use App\Repository\TemplatePartialRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PublicScreenRenderController extends AbstractController
{
    /**
     * @Route("/{username}/render/{uuid?}", name="public_screen_render")
     */
    public function publicScreenRender($uuid, $username, Request $request, ScreenRepository $screenRepository, TemplatePartialRepository $partialRepository,
                                       UserRepository $userRepository, LoggerInterface $logger, ?Profiler $profiler, ?EntityManagerInterface $em)
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
            $logger->info('User ' . $user->getId() . ' set timezone ' . $user->getTimezone() . ' failed.',
                ['caller' => 'TimezoneListener']);
        }

        $screen = $screenRepository->findOneBy(['user' => $user, 'uuid' => $uuid]);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        // Check if needs Authentication. Manage possible errors
        if (!$screen->isPublic()) {
            $response = new Response();
            $headersAuth = $request->headers->get('Authorization');
            $explodeAuth = explode(" ", $headersAuth);
            if (count($explodeAuth)>1) {
                $bearer = substr($explodeAuth[1], 0, -1);
                if (strlen($bearer)<63) {
                    $message = "BEARER Token sent is not valid (length:".strlen($bearer).")";
                    $logger->info($message);
                    $response->setContent("<h2>$message</h2>");
                    return $response;
                }
                if (strpos($screen->getOutBearer(), $bearer) === false) {
                    $message = "BEARER Token sent does not match your screen: ".$screen->getId()." configuration";
                    $logger->info($message);
                    $response->setContent("<h2>$message</h2>");
                    return $response;
                }
            } else {
                $message = "BEARER Token was not received for screen: ".$screen->getId()." and it is not public";
                $logger->info($message);
                $response->setContent("<h2>$message</h2>");
                return $response;
            }
        }

        // Basic stats
        $isPreview = $request->get('preview',0);
        if (!$isPreview) {
            $screen->incrHits();
            $em->persist($screen);
            $em->flush();
        }
        $template = $screen->getTemplateTwig();
        $partials = $partialRepository->findBy(['screen' => $screen], ['sortPos' => 'ASC']);

        $renderParams = [
            'template' => '/screen-templates/' . $template
        ];
        $htmlPerColumn['Column_1st'] = '';
        $htmlPerColumn['Column_2nd'] = '';
        $htmlPerColumn['Column_3rd'] = '';
        foreach ($partials as $p) {
            if ($p instanceof TemplatePartial) {
                $partialHtml = $this->forward($p->getIntegrationApi()->getUserApi()->getApi()->getJsonRoute(),
                    ['partial' => $p]);
                $htmlPerColumn[$p->getPlaceholder()] .= $partialHtml->getContent();

                $renderParams[$p->getPlaceholder()] = $htmlPerColumn[$p->getPlaceholder()];
            }
        }
        return $this->render(
            'public/screen-render.html.twig',
            $renderParams);
    }
}
