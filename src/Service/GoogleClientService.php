<?php
namespace App\Service;

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

    public function setAccessToken(string $accessToken) {
        $this->googleClient->setAccessToken($accessToken);
    }

    /**
     * Returns an authorized API client.
     * @return mixed
     * @throws \Exception
     * @return Google_Client the authorized client object
     */
    function getClient(string $accessToken = "")
    {
        if ($accessToken !== "") {
            $this->googleClient->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($this->googleClient->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($this->googleClient->getRefreshToken()) {
                $this->googleClient->fetchAccessTokenWithRefreshToken($this->googleClient->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $this->googleClient->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $this->googleClient->fetchAccessTokenWithAuthCode($authCode);
                $this->googleClient->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
        }
        return $this->googleClient;

    }
}