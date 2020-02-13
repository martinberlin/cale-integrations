<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Form\Api\ApiConfigureSelectionType;
use App\Form\Api\IntegrationWeatherApiType;
use App\Form\Api\Wizard\ApiDeleteConfirmationType;
use App\Form\Api\Wizard\ApiTokenType;
use App\Form\Api\Wizard\Google\GoogleCalendar1Type;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
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

/**
 * @Route("/backend/api")
 */
class BackendApiController extends AbstractController
{
    /**
     * @Route("/", name="b_home_apis")
     */
    public function homeApis(UserApiRepository $userApiRepository, IntegrationApiRepository $integrationApiRepository)
    {
        // todo: Didn't work research Why:  $apis = $userApiRepository->find(['user' => $this->getUser()]);
        $apis = $this->getUser()->getUserApis();

        $list = [];
        foreach ($apis as $userApi) {
            $api = $userApi->getApi();
            $add['id'] = $userApi->getId();
            $add['doc_url'] = $api->getDocumentationUrl();
            $add['name'] = $api->getName();
            $add['url_name'] = $api->getUrlName();
            $add['hasToken'] = (is_null($userApi->getAccessToken()))?'No token':'Configured';
            $add['created'] = $userApi->getCreated();
            $add['integrations'] = $userApi->getIntegrationApis();
            $add['edit'] = '';
            $add['userapi_id'] = $userApi->getId();
            $add['edit_route'] = '';
            //$add['category'] = $api->getCategory()->getName();
            // This part needs to be dynamic or the Routing has to be smarter
            switch ($api->getUrlName()) {
                case 'cale-google':
                    $add['edit_route'] = 'b_api_wizard_cale-google';
                    $add['edit'] = $this->generateUrl($add['edit_route'], ['uuid' => $userApi->getId()]);
                    break;

                case 'weather-darksky':
                    $add['edit_route'] = 'b_api_customize_location';
                    $add['edit'] = $this->generateUrl($add['edit_route'], ['uuid' => $userApi->getId()]);
                    break;
            }

            $list[] = $add;
        }
        return $this->render(
            'backend/admin-apis.html.twig',
            [
                'title' => 'Connected APIs',
                'apis' => $list
            ]
        );
    }

    /**
     * @Route("/configure", name="b_api_configure")
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
     * @Route("/delete/{userapi_uuid}", name="b_api_delete_userapi")
     */
    public function deleteUserApi($userapi_uuid, Request $request,
        EntityManagerInterface $entityManager,   UserApiRepository $userApiRepository)
    {
        $userApi = $userApiRepository->findOneBy(['uuid' => $userapi_uuid]);
        if (!$userApi instanceof UserApi) {
            throw $this->createNotFoundException("$userapi_uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("You don't have access to this API");
        }
        $form = $this->createForm(ApiDeleteConfirmationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->get('deleteConfirm')->getData()) {
            try {
                $entityManager->remove($userApi);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $userApi->getName()." API was not deleted. ".$e->getMessage());
                return $this->redirectToRoute('b_home_apis');
            }
            $this->addFlash('success', $userApi->getApi()->getName()." API was deleted successfully");
            return $this->redirectToRoute('b_home_apis');
        }
        return $this->render(
            'backend/api/confirm-delete.html.twig',
            [
                'title' => 'Confirm API deletion',
                'form' => $form->createView()
            ]
        );
    }

    /** Helpers to avoid repeating this calls in all the APIs */
    private function getIntegrationApi(IntegrationApiRepository $intApiRepository, $intapi_uuid) {
        $api = new IntegrationApi();
        if (!is_null($intapi_uuid)) {
            $api = $intApiRepository->findOneBy(['uuid' => $intapi_uuid]);
        }
        if (!$api instanceof IntegrationApi) {
            throw $this->createNotFoundException("$intapi_uuid is not a valid integration API");
        }
        return $api;
    }

    private function getUserApi(UserApiRepository $userApiRepository, $uuid) {
        $userApi = $userApiRepository->findOneBy(['uuid'=>$uuid]);
        if (!$userApi instanceof UserApi){
            throw $this->createNotFoundException("$uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()){
            throw $this->createNotFoundException("You don't have access to API $uuid");
        }
        return $userApi;
    }

    /**
     * @Route("/customize/location/{uuid}/{intapi_uuid?}/{step?1}", name="b_api_customize_location")
     */
    public function apiCustomizeLocation(
        $uuid, $intapi_uuid, $step, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $languages = $this->getParameter('api_languages');
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
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
                return $this->redirectToRoute('b_api_customize_location',
                    [
                        'uuid' => $uuid,
                        'intapi_uuid' => $apiUuid,
                    ]);
            }
        }

        return $this->render(
            'backend/api/location-api.html.twig',
            [
                'title' => 'Step 1: Api customize location Api',
                'form'  => $form->createView(),
                'intapi_uuid' => $intapi_uuid
            ]
        );
    }

    /**
     * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
     * Calendar service preview
     * @Route("/cale-google/json/{int_api_id}", name="b_api_cale-google")
     */
    public function apiGoogleServiceCalendarJson(
        $int_api_id, Request $request, \Google_Client $googleClient,
        IntegrationApiRepository $intApiRepository)
    {
        if ($request->getClientIp() === '127.0.0.1' && (isset($_ENV['API_PROXY']))) {
            $httpClient = new Client([
                'proxy' => $_ENV['API_PROXY'],
                'verify' => false
            ]);
            $googleClient->setHttpClient($httpClient);
        }
        $intApi = $intApiRepository->findOneBy(['uuid'=>$int_api_id]);
        if ($intApi instanceof IntegrationApi === false) {
            return $this->createNotFoundException("Integrated API not found with ID $int_api_id");
        }

        $userApi = $intApi->getUserApi();
        $googleClientService = new GoogleClientService($googleClient);
        $googleClientService->setAccessToken($userApi->getJsonToken());
        $googleClientService->setCredentials($userApi->getCredentials());
        $service = new \Google_Service_Calendar($googleClientService->getClient());
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        $response = new JsonResponse();
        $response->setContent(json_encode($events));
        return $response;
    }

    /**
     * General Location API preview
     * @Route("/json/{int_api_id}", name="b_api_request")
     */
    public function apiJsonPreview(Request $request, $int_api_id = null, IntegrationApiRepository $intApiRepository)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }
        $intApi = $intApiRepository->findOneBy(['uuid'=>$int_api_id]);
        if ($intApi instanceof IntegrationApi === false) {
            return $this->createNotFoundException("Integrated API not found with ID $int_api_id");
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

    /**
     * Wizard to configure Google Oauth. Example: https://developers.google.com/calendar/quickstart/php
     * @Route("/google/calendar/{uuid}/{intapi_uuid?}/{step?1}", name="b_api_wizard_cale-google")
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
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
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