<?php
namespace App\Controller\Callback;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Repository\IntegrationApiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Auth\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend")
 */
class GoogleCallbackController extends AbstractController
{
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

        try {
            // Refresh the token if possible, else fetch a new one.
            //if ($googleClient->getRefreshToken()) {
            $accessToken = $googleClient->fetchAccessTokenWithAuthCode($code);
            /*} else {
                $googleClient->setState($state);
                // Request authorization from the user.
                $authUrl = $googleClient->createAuthUrl();
                return $this->redirect($authUrl);
            }*/

            if (array_key_exists("error",$accessToken)) {
                $this->addFlash('error', print_r($accessToken['error'],true));
            } else {
                $googleClient->setAccessToken($accessToken);
                $userApi->setJsonToken(json_encode($googleClient->getAccessToken()));
            }

        } catch (ClientException $ce) {
            $this->addFlash('error', $ce->getMessage());
        }
        //}
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
            $renderPreview = 1;
            $this->addFlash('success', 'Your API '.$intApi->getName().' was successfully authorized.');
        }

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