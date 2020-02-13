<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Form\Api\ApiConfigureSelectionType;
use App\Form\Api\IntegrationWeatherApiType;
use App\Form\Api\Wizard\ApiTokenType;
use App\Form\Api\Wizard\Google\GoogleCalendar1Type;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use App\Repository\UserRepository;
use App\Service\GoogleClientService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/backend")
 */
class BackendController extends AbstractController
{
    /**
     * @Route("/", name="b_home")
     */
    public function home()
    {
        return $this->render(
            'backend/admin-home.html.twig',
            [
                'title' => 'Admin dashboard'
            ]
        );
    }

    /**
     * @Route("/users", name="b_users")
     */
    public function users(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $users = $userRepository->findAll();

        return $this->render(
            'backend/admin-users.html.twig',
            [
                'title' => 'List users',
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/ip_location/json/{type}", name="b_iptolocation")
     */
    public function apiExternalIpToLocation(Request $r, $type)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }

        $client = HttpClient::create();
        $ip = $r->getClientIp();
        if ($ip == '127.0.0.1') {
            $response = $client->request('GET', $_ENV['API_IP_ECHO'], $options);
            if ($response->getStatusCode() === 200) {
                $ip = $response->getContent();
            } else {
                $this->createNotFoundException('IP could not be determined using API_IP_ECHO');
            }
        }

        $locationApi = str_replace("{{IP}}", $ip, $_ENV['API_IP_LOCATION']);

        $response = $client->request('GET', $locationApi, $options);
        $r = new JsonResponse();

        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            switch ($type) {
                case 'location':
                    $json = json_decode($content);
                    $content = json_encode($json->location);
                    break;
                case 'language':
                    $json = json_decode($content);
                    $content = json_encode($json->location->languages[0]);
                    break;
                case 'geo':
                    $json = json_decode($content);
                    $geo = [
                        'latitude' =>$json->latitude,
                        'longitude'=>$json->longitude,
                        'city'     =>$json->city
                    ];
                    $content = json_encode($geo);
                    break;
            }
            $r->setContent($content);
        } else {
            $r->setContent('{"error":"Location API returned status:".$response->getStatusCode()."}")');
        }
        return $r;
    }

    /**
     * Wizard to configure Google Oauth. Example: https://developers.google.com/calendar/quickstart/php
     * @Route("/api/google/calendar/{uuid}/{intapi_uuid?}/{step?1}", name="b_api_wizard_cale-google")
     */
    public function apiWizardGoogleCalendar(
        $uuid, $intapi_uuid, $step, Request $request, UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository, EntityManagerInterface $entityManager, \Google_Client $googleClient)
    {
        // DEBUG ONLY - Add your test proxy
        if ($request->getClientIp() === '127.0.0.1' && (isset($_ENV['API_PROXY']))) {
            $httpClient = new Client([
                'proxy' => $_ENV['API_PROXY'],
                'verify' => false
            ]);
            $googleClient->setHttpClient($httpClient);
        }
        $renderPreview = 0;
        $userApi = $userApiRepository->findOneBy(['uuid' => $uuid]);
        if (!$userApi instanceof UserApi) {
            throw $this->createNotFoundException("$uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("You don't have access to API $uuid");
        }
        $api = new IntegrationApi();
        if (!is_null($intapi_uuid)) {
            $api = $intApiRepository->findOneBy(['uuid' => $intapi_uuid]);
        }
        if (!$api instanceof IntegrationApi) {
            throw $this->createNotFoundException("$intapi_uuid is not a valid integration API");
        }
        $authUrl = '';
        switch ($step) {
            case 2:
                if ($userApi->getJsonToken()!=="") {
                    $renderPreview = 1;
                }
                $title = "Step 2: Accept read-only access and copy the generated Token";
                $form = $this->createForm(ApiTokenType::class, $userApi);
                $googleClient->setApplicationName($this->getParameter('google_application_name'));
                $googleClient->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
                $googleClient->setAccessType('offline'); // offline
                $googleClient->setPrompt('select_account consent');
                if (!is_null($userApi->getCredentials())) {
                    $credentials = json_decode($userApi->getCredentials(),true);
                    $key = isset($credentials['installed']) ? 'installed' : 'web';

                    foreach ($credentials[$key] as $ck=>$cv) {
                        $googleClient->setConfig($ck,$cv);
                        switch ($ck) {
                            case 'client_id':
                                $googleClient->setClientId($cv);
                                break;
                            case 'client_secret':
                                $googleClient->setClientSecret($cv);
                                break;
                            case 'redirect_uris':
                                $googleClient->setRedirectUri($cv[0]);
                                break;
                        }
                    }
                    // Request authorization from the user.
                    $authUrl = $googleClient->createAuthUrl();
                }

                break;
            default:
                $title = 'Step 1: Turn on the Google Calendar API';
                $form = $this->createForm(GoogleCalendar1Type::class, $api);
        }

        $form->handleRequest($request);
        $error = "";
        $apiUuid = "";

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($step) {
                case 1:
                    $credentialsFileUpload = $form->get('credentialsFile')->getData();
                    if ($credentialsFileUpload) {
                        $userApi->setCredentials(file_get_contents($credentialsFileUpload->getPathname()));
                    } else {
                        $this->addFlash('error', "Error reading credentials file");
                    }

                    $api->setUserApi($userApi);
                    try {
                        $entityManager->persist($api);

                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                        $this->addFlash('error', $error);
                    }
                    break;
                case 2:
                    // Exchange authorization code for an access token.
                    try {
                        $accessToken = $googleClient->fetchAccessTokenWithAuthCode($userApi->getAccessToken());
                        $googleClient->setAccessToken($accessToken);
                        $userApi->setJsonToken(json_encode($googleClient->getAccessToken()));
                    } catch (ClientException $ce) {
                        $this->addFlash('success', $ce->getMessage());
                    }
                    break;
            }

            if ($error === '') {
                $entityManager->persist($userApi);
                $entityManager->flush();
                $this->addFlash('success', 'Credentials saved');
                $apiUuid = $api->getId();
                return $this->redirectToRoute('b_api_wizard_cale-google',
                    [
                        'uuid' => $uuid,
                        'intapi_uuid' => $apiUuid,
                        'step' => 2
                    ]);
            }
        }
        return $this->render(
            "backend/api/wizard/google/cale-google-{$step}.html.twig",
            [
                'title' => $title,
                'form'  => $form->createView(),
                'step'  => $step,
                'authUrl' => $authUrl,
                'api_uuid' => $apiUuid,
                'intapi_uuid' => $intapi_uuid,
                'renderPreview' => $renderPreview
            ]
        );
    }

    }
