<?php
namespace App\Controller\Render;

use App\Entity\Screen;
use App\Entity\TemplatePartial;
use App\Entity\User;
use App\Repository\ScreenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
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
    public function publicScreenRender($uuid, $username, Request $request, ScreenRepository $screenRepository, UserRepository $userRepository,
                                       LoggerInterface $logger, ?Profiler $profiler, ?EntityManagerInterface $em)
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
        // Basic stats
        $isPreview = $request->get('preview',0);
        if (!$isPreview) {
            $screen->incrHits();
            $em->persist($screen);
            $em->flush();
        }
        $template = $screen->getTemplateTwig();
        $partials = $screen->getPartials();

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

                $renderParams[$p->getPlaceholder()] = [
                    'content' => $htmlPerColumn[$p->getPlaceholder()]
                ];
            }
        }
        return $this->render(
            'public/screen-render.html.twig',
            $renderParams);
    }

    /**
     * @Route("/{username}/bmp/{uuid?}", name="public_screen_bitmap")
     */
    public function publicScreenFetchBitmap($uuid, $username, Request $request,
                                            ScreenRepository $screenRepository, UserRepository $userRepository, ?Profiler $profiler) {
        if (null !== $profiler) {
            $profiler->disable();
        }
        $user = $userRepository->findOneBy(['name' => $username]);
        if (!$user instanceof User) {
            throw $this->createNotFoundException("This user does not exist. Please check the URL");
        }
        $screen = $screenRepository->findOneBy(['user' => $user, 'uuid' => $uuid]);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        // Start fetch image with HttpClient
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }
        $htmlUrl = $this->generateUrl('public_screen_render', [
            'username' => $this->getUser()->getName(),
            'uuid'     => $uuid
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $htmlUrl = "https://cale.es/fasani/render/5e4d6dd0bd483"; // DEBUG - - - - - -

        $testUrl = "http://calendar.fasani.de/screenshot/cale.php?u=http://calendar.fasani.de/paulina&w=800&h=480&d=1";
        $client = HttpClient::create();
        $httpResponse = $client->request('GET', $testUrl, $options);
        $response = new Response();
        $response->setContent($httpResponse->getContent());
        $response->headers->set('Content-type', 'image/bmp');
        return $response;
    }
}
