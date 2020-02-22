<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class GoogleClientService
{
    private $credentials;
    private $accessToken;
    protected $googleClient;

    // Todo: Pass scope so it can work for another Google APIs
    public function __construct(\Google_Client $googleClient) {
        $this->googleClient = $googleClient;
        $this->googleClient->setApplicationName('Cale Web client');
        $this->googleClient->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
        $this->googleClient->setAccessType('online');
        $this->googleClient->setPrompt('select_account consent');

    }

    public function setCredentials(string $myCredentials) {
        $this->credentials = $myCredentials;
        $credentials = json_decode($myCredentials,true);
        $key = isset($credentials['installed']) ? 'installed' : 'web';

        foreach ($credentials[$key] as $ck=>$cv) {
            $this->googleClient->setConfig($ck,$cv);
            switch ($ck) {
                case 'client_id':
                    $this->googleClient->setClientId($cv);
                    break;
                case 'client_secret':
                    $this->googleClient->setClientSecret($cv);
                    break;
                case 'redirect_uris':
                    $this->googleClient->setRedirectUri($cv[0]);
                    break;
            }
        }
    }

    /**
     * @param $accessToken
     * @return Response / Google_Client
     */
    public function setAccessToken($accessToken) {
        try {
            $this->googleClient->setAccessToken($accessToken);
        } catch(\InvalidArgumentException $e) {
            $response = new Response();
            $response->setContent('We could not connect with the API. Please try to configure it again in APIs menu. '.$e->getMessage());
            return $response;
        }
    }

    /**
     * Returns an authorized API client.
     * @return mixed
     * @throws \Exception
     * @return Google_Client / mixed the authorized client object
     */
    function getClient(string $accessToken = "")
    {
        if ($accessToken!=="") {
            $this->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($this->googleClient->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($this->googleClient->getRefreshToken()) {
                $this->googleClient->fetchAccessTokenWithRefreshToken($this->googleClient->getRefreshToken());
            } else {
                // Check to see if there was an error.
                $error = "";
                if (is_array($accessToken) && array_key_exists('error', $accessToken)) {
                    $error .= join(', ', $accessToken);
                }
                // Render authorization link for the user
                $authUrl = $this->googleClient->createAuthUrl();
                $response = new Response();
                $response->setContent('<a href="'.$authUrl.'">Click here to revalidate your access to the API</a><br><br>
                <small>'.$error.'</small>');
                return $response;
            }
        }
        return $this->googleClient;

    }
}