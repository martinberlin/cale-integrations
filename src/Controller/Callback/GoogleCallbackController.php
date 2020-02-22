<?php
namespace App\Controller\Callback;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Repository\IntegrationApiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Auth\Cache\InvalidArgumentException;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend")
 */
class GoogleCallbackController extends AbstractController
{
    public function setGoogleClient(\Google_Client &$googleClient, $scope) {
        // Check that this API belongs to this user //dump($userApi->getUser(),$this->getUser());exit();
        $googleClient->setApplicationName($this->getParameter('google_application_name'));
        $googleClient->setScopes($scope);
        $googleClient->setAccessType('online');
        $googleClient->setPrompt('select_account consent');

        $envCredentials = $_ENV['OAUTH_GOOGLE_CALENDAR_CREDENTIALS'];

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
    }

    /**
     * @Route("/google/callback", name="b_callback_google")
     *
     * @param Request $request
     * @param IntegrationApiRepository $apiRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function apiGoogleCallback(Request $request,
                                      IntegrationApiRepository $apiRepository,
                                      EntityManagerInterface $entityManager,
                                      \Google_Client $googleClient)
    {
        $code = $request->get('code');
        $scope = $request->get('scope');
        $state = $request->get('state'); // Contains the $intapi_uuid
        $intApi = $apiRepository->findOneBy(['uuid' => $state]);
        $title = "Authorization status";
        if (!$intApi instanceof IntegrationApi) {
            throw $this->createNotFoundException("API not found");
        }

        $userApi = $intApi->getUserApi();
        $renderPreview = 0;
        $this->setGoogleClient($googleClient, $scope);

        try {
            // Fetch a new token
            $accessToken = $googleClient->fetchAccessTokenWithAuthCode($code);

            if (array_key_exists("error",$accessToken)) {
                $error = print_r($accessToken['error'],true);
                $this->addFlash('error', $error);
            } else {
                $googleClient->setAccessToken($accessToken);
                $userApi->setJsonToken(json_encode($googleClient->getAccessToken()));
            }

        } catch (ClientException $e) {
            $error = $e->getMessage();
            $this->addFlash('error', $e->getMessage());
        }

        // Save the authorization code
        $userApi->setAccessToken($code);
        $userApi->setIsConfigured(true);
        try {
            $entityManager->persist($userApi);
            $entityManager->flush();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->addFlash('error', $error);
        }
        if (!isset($error)) {
            $this->addFlash('success', 'Your API '.$intApi->getName().' was successfully authorized. Now is ready to be added in your Screens');
        }
        $renderPreview = 1;
        return $this->render(
            "backend/api/wizard/google/cale-google-callback.html.twig",
            [
                'title' => $title,
                'intapi_uuid' => $state,
                'renderPreview' => $renderPreview // 1 enables the javascript preview
            ]
        );
    }
}