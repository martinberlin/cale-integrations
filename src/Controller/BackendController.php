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
use Doctrine\ORM\EntityManagerInterface;
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
     * @Route("/api/configure", name="b_api_configure")
     */
    public function apiConfigure(Request $request, EntityManagerInterface $entityManager)
    {
        $userApi = new UserApi();
        $form = $this->createForm(ApiConfigureSelectionType::class, $userApi);
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $userApi->setUser($this->getUser());
            try {
                $entityManager->persist($userApi);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error === '') {
                $this->addFlash('success', 'API is connected with your user');
                $userApiUuidParameter = ['uuid' => $userApi->getId()];
                $api = $userApi->getApi(); // todo: think about smarter way to do this

                if ($api->isLocationApi()) {
                   return $this->redirectToRoute('b_api_customize_location', $userApiUuidParameter);
                }
                switch ($api->getUrlName()) {
                    case 'cale-google':
                        return $this->redirectToRoute('b_api_wizard_'.$api->getUrlName(), $userApiUuidParameter);
                        break;
                }
            }
        }

        return $this->render(
            'backend/api/configure-api.html.twig',
            [
                'title' => 'Api configurator',
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/api/google/calendar/{uuid}/{intapi_uuid}/{step}", name="b_api_wizard_cale-google")
     */
    public function apiWizardGoogleCalendar(
        $uuid, $intapi_uuid = "", $step = 1, Request $request, UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository, EntityManagerInterface $entityManager, \Google_Client $googleClient)
    {
        $userApi = $userApiRepository->findOneBy(['uuid' => $uuid]);
        if (!$userApi instanceof UserApi) {
            throw $this->createNotFoundException("$uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("You don't have access to API $uuid");
        }
        $api = new IntegrationApi();
        if ($intapi_uuid !== "") {
            $api = $intApiRepository->findOneBy(['uuid' => $intapi_uuid]);
        }
        if (!$api instanceof IntegrationApi) {
            throw $this->createNotFoundException("$intapi_uuid is not a valid integration API");
        }
        $authUrl = '';
        switch ($step) {
            case 2:
                $title = "Step 2: Accept read-only access and copy the generated Token";
                $form = $this->createForm(ApiTokenType::class, $userApi);
                $googleClient->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
                $googleClient->setAccessType('online'); // offline
                $googleClient->setPrompt('select_account consent');
                $credentials = json_decode($userApi->getCredentials(),true);
                $key = isset($credentials['installed']) ? 'installed' : 'web';
                //dump($credentials[$key]);exit();
                foreach ($credentials[$key] as $ck=>$cv) {
                    $googleClient->setConfig($ck,$cv);
                    switch ($ck) {
                        case 'client_id':
                            $googleClient->setClientId($cv);
                            break;
                        case 'client_secret':
                            $googleClient->setClientSecret($cv);
                            break;
                    }
                }
                $redirectUri = $this->generateUrl('b_api_wizard_cale-google',
                    [
                        'uuid' => $uuid,
                        'intapi_uuid' => $intapi_uuid,
                        'step' => 2
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                $googleClient->setRedirectUri($redirectUri);
                // Request authorization from the user.
                $authUrl = $googleClient->createAuthUrl();

                break;
            default:
                $title = 'Step 1: Turn on the Google Calendar API';
                $form = $this->createForm(GoogleCalendar1Type::class, $api);
        }

        $form->handleRequest($request);
        $error = "";
        $apiUuid = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $credentialsFileUpload = $form->get('credentialsFile')->getData();

            if ($step === 1) {
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
                'api_uuid' => $apiUuid,
                'step'  => $step,
                'authUrl' => $authUrl
            ]
        );

    }

    /**
     * TODO: This uuid is of the User API but on saving should set also the uuid of IntApi
     *       since with same api token you could query different cities
     * @Route("/api/customize/location/{uuid}", name="b_api_customize_location")
     */
    public function apiCustomizeLocation(
        $uuid, Request $request,UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository, EntityManagerInterface $entityManager)
    {
        $step = 1; // Default wizard step
        $languages = $this->getParameter('api_languages');
        $userApi = $userApiRepository->findOneBy(['uuid'=>$uuid]);
        if (!$userApi instanceof UserApi){
            throw $this->createNotFoundException("$uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()){
            throw $this->createNotFoundException("You don't have access to API $uuid");
        }

        $api = $intApiRepository->findOneBy(['userApi'=>$userApi]);
        if ($api instanceof IntegrationApi === false) {
            $api = new IntegrationApi();
        }
        if ($this->getUser()->getLanguage()!=="" && $api->getLanguage() === "") {
          $api->setLanguage($this->getUser()->getLanguage());
        }

        $api->setJsonSettings($userApi->getApi()->getDefaultJsonSettings());

        $form = $this->createForm(IntegrationWeatherApiType::class, $api,
            [
                'languages' => array_flip($languages)
            ]);
        $form->handleRequest($request);
        $error = "";
        $apiUuid = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $api->setUserApi($userApi);

            try {
                $entityManager->persist($api);

            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error === '') {
                $userApi->setIsConfigured(true);
                $entityManager->persist($userApi);
                $entityManager->flush();
                $this->addFlash('success', 'Location API configuration saved');
                $apiUuid = $api->getId();
            }
        }

        return $this->render(
            'backend/api/location-api.html.twig',
            [
                'title' => 'Step 1: Api customize location Api',
                'form'  => $form->createView(),
                'api_uuid' => $apiUuid,
                'step'  => $step
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
            $options = array('proxy' => $_ENV['API_PROXY']);
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
     * @Route("/api/rest/{uuid}", name="b_api_request")
     */
    public function apiJsonPreview(Request $request, $uuid = null, IntegrationApiRepository $intApiRepository)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => $_ENV['API_PROXY']);
        }
        $intApi = $intApiRepository->findOneBy(['uuid'=>$uuid]);
        if ($intApi instanceof IntegrationApi === false) {
           return $this->createNotFoundException("Integrated API not found with ID $uuid");
        }

        // https://api.darksky.net/forecast/[token]/[latitude],[longitude]
        $userApi = $intApi->getUserApi();
        $api = $userApi->getApi();

        $apiUrl = str_replace("[token]", $userApi->getAccessToken(), $api->getUrl());
        $apiUrl = str_replace("[latitude]", $intApi->getLatitude(), $apiUrl);
        $apiUrl = str_replace("[longitude]", $intApi->getLongitude(), $apiUrl);

        if ($intApi->getJsonSettings() !== '') {
            try {
                $extraParams = json_decode($intApi->getJsonSettings(), true);
            } catch (\Exception $e) {
                return $this->createNotFoundException("Failed parsing json settings for API. ".$e->getMessage());
            }
            // Language is an exception since is set in the Configure API on IntegrationApi level
            $extraParams['lang'] = $intApi->getLanguage();
            $apiUrl.= '?'.http_build_query($extraParams);
        }

        $client = HttpClient::create();
        $rest = $client->request('GET', $apiUrl, $options);
        $response = new JsonResponse();

        if ($rest->getStatusCode() === 200) {
            $response->setContent($rest->getContent());
        } else {
            $response->setContent(json_encode(['status' => $rest->getStatusCode(),'message' => 'API rest call failed']));
        }
        return $response;
    }

    }
