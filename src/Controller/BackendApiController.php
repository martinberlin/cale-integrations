<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Form\Api\ApiConfigureSelectionType;
use App\Form\Api\Crypto\IntegrationEtherscanType;
use App\Form\Api\IntegrationAwsCloudwatchType;
use App\Form\Api\IntegrationAwsType;
use App\Form\Api\IntegrationHtmlType;
use App\Form\Api\IntegrationSharedCalendarApiType;
use App\Form\Api\IntegrationWeatherApiType;
use App\Form\Api\Wizard\ApiDeleteConfirmationType;
use App\Form\Api\Wizard\ApiTokenType;
use App\Form\Api\Wizard\Google\GoogleCalendar1Type;
use App\Repository\ApiRepository;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/backend/api")
 */
class BackendApiController extends AbstractController
{
    private $menu = "api";
    /**
     * @Route("/", name="b_home_apis")
     */
    public function homeApis(Request $request, UserApiRepository $userApiRepository, IntegrationApiRepository $integrationApiRepository,
                             BackendController $backendController, TranslatorInterface $translator)
    {
        $apis = $this->getUser()->getUserApis();

        $list = [];
        foreach ($apis as $userApi) {
            $api = $userApi->getApi();
            $add['id'] = $userApi->getId();
            $add['doc_url'] = $api->getDocumentationUrl();
            $add['name'] = $api->getName();
            $add['url_name'] = $api->getUrlName();
            $add['hasToken'] = (is_null($userApi->getAccessToken()))?'No token':'ok';
            $add['created'] = $userApi->getCreated();
            $add['integrations'] = $userApi->getIntegrationApis();
            $add['edit'] = '';
            $add['userapi_id'] = $userApi->getId();
            $add['edit_route'] = $api->getEditRoute();
            $add['is_configured'] = $userApi->isConfigured();
            if ($add['edit_route'] !=='' && is_null($add['edit_route'])===false) {
                $add['edit'] = $this->generateUrl($api->getEditRoute(), ['uuid' => $userApi->getId()]);
                $list[] = $add;
            } else {
                $this->addFlash('error', 'No edit route configured for API #'.$add['id'].' '.$add['name']);
            }
        }
        return $this->render(
            'backend/admin-apis.html.twig',
            [
                'title' => $translator->trans('titleb_apis'),
                'apis' => $list,
                'isMobile' => $backendController->isMobile($request),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/configure", name="b_api_configure")
     */
    public function apiConfigure(Request $request, EntityManagerInterface $entityManager, ApiRepository $apiRepository)
    {
        $getApis = $apiRepository->findAll();
        $apis = [];
        foreach ($getApis as $api) {
            $apis[$api->getId()] = $api->getAuthNote();
        }
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
                // TODO: Rethink how to do a smart selection of the configuration tool. Remove the isLocationApi boolean IMPORTANT!
                if ($api->isLocationApi()) {
                    return $this->redirectToRoute('b_api_customize_location', $userApiUuidParameter);
                }

                return $this->redirectToRoute($api->getEditRoute(), $userApiUuidParameter);
            }
        }

        return $this->render(
            'backend/api/configure-api.html.twig',
            [
                'title' => 'New content item',
                'form' => $form->createView(),
                'json_apis' => json_encode($apis),
                'menu' => $this->menu
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
                'title' => 'Confirm API removal',
                'form' => $form->createView(),
                'menu' => $this->menu
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
        if (is_null($api->getJsonSettings()) || $api->getJsonSettings() ==='') {
            $api->setJsonSettings($userApi->getApi()->getDefaultJsonSettings());
        }
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
            'backend/api/conf-location-api.html.twig',
            [
                'title' => 'Step 1: Setup location Api',
                'form'  => $form->createView(),
                'intapi_uuid' => $intapi_uuid,
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/calendar/shared/{uuid}/{intapi_uuid?}/{step?1}", name="b_api_wizard_cale-timetree")
     */
    public function apiSharedCalendar(
        $uuid, $intapi_uuid, $step, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        if (is_null($api->getJsonSettings()) || $api->getJsonSettings() ==='') {
            $api->setJsonSettings($userApi->getApi()->getDefaultJsonSettings());
        }

        $form = $this->createForm(IntegrationSharedCalendarApiType::class, $api);
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $api->setUserApi($userApi);
            try {
                $entityManager->persist($api);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }

            if ($error === '') {
                $this->addFlash('success', "Saved");

                return $this->redirectToRoute('b_api_wizard_cale-timetree',
                    ['uuid' => $userApi->getId(), 'intapi_uuid' => $api->getId(), 'step' => 2]);
            }
        }
        return $this->render(
            'backend/api/conf-shared-calendar-api.html.twig',
            [
                'title' => 'Step 1: Configure shared calendar',
                'form'  => $form->createView(),
                'intapi_uuid' => $intapi_uuid,
                'userapi_id'  => $userApi->getId(),
                'menu' => $this->menu
            ]
        );
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
        if (is_null($api->getJsonSettings()) || $api->getJsonSettings() ==='') {
            $api->setJsonSettings($userApi->getApi()->getDefaultJsonSettings());
        }
        $authUrl = '';
        switch ($step) {
            case 2:
                if ($userApi->getJsonToken()!=="") {
                    $renderPreview = 1;
                }
                $title = "Step 2: Accept read-only access so we can read your events";
                $form = $this->createForm(ApiTokenType::class, $userApi);
                $googleClient->setApplicationName($this->getParameter('google_application_name'));
                $googleClient->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
                $googleClient->setAccessType('offline');
                $googleClient->setPrompt('select_account consent');

                $envCredentials = $_ENV['OAUTH_GOOGLE_CALENDAR_CREDENTIALS'];

                if (!is_null($envCredentials)) {
                    $credentials = json_decode($envCredentials,true);
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
                    // Pass the uuid to get it back on the callback
                    $googleClient->setState($intapi_uuid);
                    // Request authorization from the user.
                    $authUrl = $googleClient->createAuthUrl();
                }

                break;
            default:
                $title = 'Step 1: Connect with the Google Calendar API';
                $form = $this->createForm(GoogleCalendar1Type::class, $api);
        }

        $form->handleRequest($request);
        $error = "";
        $apiUuid = "";

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($step) {
                case 1:
                    $api->setUserApi($userApi);
                    try {
                        $entityManager->persist($api);

                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                        $this->addFlash('error', $error);
                    }
                    break;
            }

            if ($error === '') {
                $entityManager->persist($userApi);
                $entityManager->flush();
                $this->addFlash('success', 'Name saved');
                $apiUuid = $api->getId();
                return $this->redirectToRoute('b_api_wizard_cale-google',
                    [
                        'uuid' => $uuid,
                        'intapi_uuid' => $apiUuid,
                        'step' => 2
                    ]);
            }
        }

        if ($userApi->isConfigured()) {
            $this->addFlash('success', 'Your API '.$api->getName().' is marked as configured. Do this again only if you can not access your Calendar');
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
                'renderPreview' => $renderPreview,
                'menu' => $this->menu
            ]
        );
    }

    /**
     * Wizard to configure HTML internal API
     * @Route("/html/{uuid}/{intapi_uuid?}", name="b_api_wizard_cale-html")
     */
    public function apiInternalHtml(
        $uuid, $intapi_uuid, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $publicRelativePath = '../public';
        $htmlMaxChars = $this->getParameter('html_max_chars');
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        if (!$api instanceof IntegrationApi) {
            $api = new IntegrationApi();
        }
        $form = $this->createForm(IntegrationHtmlType::class, $api, [
            'html_max_chars' => $htmlMaxChars
        ]);
        $form->handleRequest($request);
        $error = "";$preSuccessMsg = "";
        $imageUploaded = false;
        if ($form->getClickedButton()) {
            switch ($form->getClickedButton()->getName()) {

                case 'remove_image':
                    try {
                        $removeFlag = unlink($publicRelativePath . $api->getImagePath());
                    } catch (\ErrorException $e) {
                        $this->addFlash('error', "Could not find image. ");
                        $removeFlag = false;
                    }
                    if ($removeFlag) {
                        $api->setImagePath('');
                        $preSuccessMsg = "Image was removed. ";
                    }
                    break;

                case 'remove_html':
                    $this->addFlash('success', "The HTML api integration ".$api->getName()." was removed");
                    $entityManager->remove($api);
                    $entityManager->flush();
                    return $this->redirectToRoute('b_home_apis');
                    break;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            // This condition is needed because the 'imageFile' field is not required
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL. We will allow only one image per HTML API so name does not matter:
                // $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $api->getId() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                $imagePublicPath = $this->getParameter('screen_images_directory') . '/' . $this->getUser()->getId();
                $imageUploadPath = $publicRelativePath.$imagePublicPath;

                try {
                    $imageUploaded = true;
                    $imageFile->move(
                        $imageUploadPath,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $error = $e->getMessage();
                    $imageUploaded = false;
                }

                $api->setImagePath($imagePublicPath.'/'.$newFilename);

            }
            $userApi->setIsConfigured(true);
            $api->setUserApi($userApi);
            try {
                $entityManager->persist($api);
                $entityManager->flush();
            }
            catch (UniqueConstraintViolationException $e) {
                $error = '"'.$api->getName().'" exists already. Please use another name for this HTML element (Name your API)';
                $this->addFlash('error', $error);
            }
            catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }

            if ($error === '') {
                $this->addFlash('success', $preSuccessMsg." HTML content saved");
                return $this->redirectToRoute('b_api_wizard_cale-html',
                    [
                        'uuid' => $userApi->getId(),
                        'intapi_uuid' => $api->getId()
                    ]);
            }
        }

        return $this->render(
            'backend/api/conf-html-content.html.twig',
            [
                'title' => 'Step 1: Write your HTML content',
                'form' => $form->createView(),
                'intapi_uuid' => $intapi_uuid,
                'userapi_id' => $userApi->getId(),
                'date_format' => $this->getUser()->getDateFormat(),
                'hour_format' => $this->getUser()->getHourFormat(),
                'image_path' => $api->getImagePath(),
                'html_max_chars' => $htmlMaxChars,
                'menu' => $this->menu
            ]
        );
    }


    /**
     * Wizard to configure HTML internal API
     * @Route("/aws/cloudfront/{uuid}/{intapi_uuid?}/{step?1}", name="b_api_wizard_aws-cloudwatch")
     */
    public function apiAwsCloudwatch(
        $uuid, $intapi_uuid, $step, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = null;
        if ($intapi_uuid !== 'new') {
           $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        }
        if (!$api instanceof IntegrationApi) {
            $api = new IntegrationApi();
        }
        $template = 'backend/api/aws/conf-aws-credentials.html.twig';
        switch ($step) {
            // General AWS Credentials
            case 1:
                $form = $this->createForm(IntegrationAwsType::class, $userApi);
                $title = 'Setup your Amazon Credentials';
                break;
            // This particular AWS service
            case 2:
                $form = $this->createForm(IntegrationAwsCloudwatchType::class, $api);
                $template = 'backend/api/aws/conf-cloudwatch.html.twig';
                $title = 'Add a Cloudfront widget';
                break;
        }

        $form->handleRequest($request);
        $error = "";
        $formValid = $form->isSubmitted() && $form->isValid();
        if ($formValid) {
            switch ($step) {
                case 1:
                    try {
                        $userApi->setIsConfigured(true);
                        $entityManager->persist($userApi);
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                        $this->addFlash('error', $error);
                        return $this->redirectToRoute('b_api_wizard_aws-cloudwatch',
                            ['uuid' => $userApi->getId(), 'step' => 1]);
                    }
                    if ($error === '') {
                        $this->addFlash('success', "AWS Credentials saved");
                    }
                    return $this->redirectToRoute('b_api_wizard_aws-cloudwatch',
                        ['uuid' => $userApi->getId(), 'intapi_uuid' => 'new', 'step' => 2]);
                    break;
                case 2:
                    $api->setUserApi($userApi);
                    try {
                        $entityManager->persist($api);
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                        $this->addFlash('error', $error);
                    }
                    if ($error === '') {
                        $this->addFlash('success', "Cloudfront metric configuration saved");
                    }
                    break;
            }

            return $this->redirectToRoute('b_api_wizard_aws-cloudwatch',
                ['uuid' => $userApi->getId(), 'intapi_uuid' => $api->getId(), 'step' => 2]);
        }

        return $this->render(
            $template,
            [
                'title' => $title,
                'form' => $form->createView(),
                'intapi_uuid' => $intapi_uuid,
                'userapi_id' => $userApi->getId(),
                'form_valid' => $formValid,
                'menu' => $this->menu
            ]
        );
    }


    /**
     * @Route("/crypto/etherscan/{uuid}/{intapi_uuid?}/{step?1}", name="api_etherscan")
     */
    public function apiEtherscan(
        $uuid, $intapi_uuid, $step, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        if (is_null($api->getJsonSettings()) || $api->getJsonSettings() ==='') {
            $api->setJsonSettings($userApi->getApi()->getDefaultJsonSettings());
        }

        $form = $this->createForm(IntegrationEtherscanType::class, $api);
        // If step is 2 then prefill the form
        $decodeJson = json_decode($api->getJsonSettings(), true);
        if (in_array('address', $decodeJson)) {
            $form->get('address')->setData($decodeJson['address']);
            $form->get('numberOfTransactions')->setData($decodeJson['numberOfTransactions']);
            $form->get('showConversionPrice')->setData($decodeJson['showConversionPrice']);
            $form->get('actionQuery')->setData($decodeJson['action']);
        }
        $form->handleRequest($request);
        $error = "";
        $actionQuery = $form->get('actionQuery')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            // handle non-mapped: showTransactions, showConversionPrice
            $data = array();
            $data['address'] = $form->get('address')->getData();
            $data['action'] = $form->get('actionQuery')->getData();
            $data['numberOfTransactions'] = ($form->get('numberOfTransactions')->getData()) ?? 0;
            $data['showConversionPrice'] = $form->get('showConversionPrice')->getData();
            $defaultJsonArray = [];
            try {
                $defaultJsonArray = json_decode($userApi->getApi()->getDefaultJsonSettings(), true);
            } catch (\Exception $e) {
                // Warn about default settings missing
            }
            $defaultJsonArray = array_merge($data,$defaultJsonArray);
            $userApi->setIsConfigured(true);
            $api->setJsonSettings(json_encode($defaultJsonArray));
            $api->setUserApi($userApi);
            try {
                $entityManager->persist($api);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }

            if ($error === '') {
                $this->addFlash('success', ($actionQuery==="balance")?"Saved":
                    "Saved. Keep in mind that the transactions list will work good only in big screens (WIDTH >= 400px)");
                return $this->redirectToRoute('api_etherscan',
                    ['uuid' => $userApi->getId(), 'intapi_uuid' => $api->getId(), 'step' => 2]);
            }
        }
        return $this->render(
            'backend/api/crypto/conf-etherscan.html.twig',
            [
                'title' => 'Configure Etherscan.io API',
                'form'  => $form->createView(),
                'intapi_uuid' => $intapi_uuid,
                'intapi'      => $api,
                'userapi_id'  => $userApi->getId(),
                'action_query' => $actionQuery,
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/crypto/json/ether_balance/{intapi_uuid?}/{action?}", name="json_etherscan")
     */
    public function jsonEtherBalance(Request $r, $intapi_uuid, $action, IntegrationApiRepository $intApiRepository)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }
        $api = $intApiRepository->findOneBy(['uuid' => $intapi_uuid]);
        if (!$api instanceof IntegrationApi) {
           throw $this->createNotFoundException("$intapi_uuid is not a valid integration API");
        }
        $userApi = $api->getUserApi();
        $apiConfig = $userApi->getApi();
        $apiKey = $userApi->getAccessToken();
        $jsonConfig = json_decode($api->getJsonSettings());
        // Prepare URL
        // https://api.etherscan.io/api?module=[module]&action=[action]&address=[address]&apikey=[apikey]
        $url = str_replace('[module]','account', $apiConfig->getUrl());
        $url = str_replace('[action]',$action, $url);
        $url = str_replace('[address]',$jsonConfig->address, $url);
        $url = str_replace('[apikey]',$apiKey, $url);
        $client = HttpClient::create();
        $response = $client->request('GET', $url, $options);
        $r = new JsonResponse();
        if ($response->getStatusCode() === 200) {
            $r->setContent($response->getContent());
        } else {
            $r->setContent('{"error":"Etherscan API returned status code:"'.$response->getStatusCode().'"}")');
        }
        return $r;
    }

}