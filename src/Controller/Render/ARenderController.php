<?php
namespace App\Controller\Render;

use App\Entity\IntegrationApi;
use App\Entity\SysLog;
use App\Entity\TemplatePartial;
use App\Entity\UserApi;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use App\Service\GoogleClientService;
use App\Service\SimpleCacheService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Here there are two methods per API - One retrieves the JSON data - 2nd renders the partial
 * @Route("/json")
 */
class ARenderController extends AbstractController
{
    private function convertDateTime($unixTime,$hourFormat) {
        $dt = new \DateTime("@$unixTime");
        return $dt->format($hourFormat);
    }

    /**
     * NOTE: As a difference with other APIs google services will call the API directly without JSON first
     * render_google_calendar internally called. This reads the API data and responds with an HTML content part
     * @Route("/render/google_calendar", name="render_google_calendar")
     */
    public function render_google_calendar(TemplatePartial $partial,
                                           IntegrationApiRepository $intApiRepository, EntityManagerInterface $em,
                                           Request $request, \Google_Client $googleClient)
    {

    if ($request->getClientIp() === '127.0.0.1' && (isset($_ENV['API_PROXY']))) {
        $httpClient = new Client([
            'proxy' => $_ENV['API_PROXY'],
            'verify' => false
        ]);
        $googleClient->setHttpClient($httpClient);
    }
        // Read user preferences
        $user = $partial->getScreen()->getUser();
        $dateFormat = $user->getDateFormat();
        $hourFormat = $user->getHourFormat();
        $intApi = $partial->getIntegrationApi();

        // Tell here already the client what is our API id
        $googleClient->setState($intApi->getId());
        if ($intApi instanceof IntegrationApi === false) {
            return $this->createNotFoundException("Integrated API not found for partial: ".$partial->getId());
        }

        $userApi = $intApi->getUserApi();
        $jsonToken = json_decode($userApi->getJsonToken());
        // Set refresh token
        $log = new SysLog();
        $log->setEntityName('userApi');
        $log->setEntityId($userApi->getId());
        $log->setUser($userApi->getUser());
        $log->setType('oauth');
        if (json_last_error() === JSON_ERROR_NONE && property_exists($jsonToken, 'refresh_token')) {
            $googleClient->refreshToken($jsonToken->refresh_token);
        } else {
            $log->setMessage('Google Calendar refreshToken was not found in jsonToken');
            $em->persist($log);
            $em->flush();
        }

        $googleClientService = new GoogleClientService($googleClient);

        $tokenResponse = $googleClientService->setAccessToken($userApi->getJsonToken());
        if ($tokenResponse instanceof Response) {
            // Not valid token, show error but keep on rendering
            return $tokenResponse;
        }
        $googleClientService->setCredentials($_ENV['OAUTH_GOOGLE_CALENDAR_CREDENTIALS']);
        $getClient = $googleClientService->getClient();
        if ($getClient instanceof Response) {
            // Not valid instantiation of the Client, show error but keep on rendering
            return $getClient;
        }
        $service = new \Google_Service_Calendar($getClient);
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => $partial->getMaxResults(),
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();
        $responseContent = '';
        // Start HTML building - Headlines is a try to mould this to Screen environment
        $hs = (substr($partial->getScreen()->getTemplateTwig(), 0, 1) > 1) ? 'h4' : 'h3';
        $colorClass = ($partial->getInvertedColor())?'inverted_color':'default_color';
        $eventClass = ($partial->getInvertedColor()) ? 'default_color' : 'inverted_color';
        $iconArrowRight = ' <span class="glyphicon glyphicon-arrow-right"></span>';

        $responseContent = '<div class="row '.$colorClass.'"><div class="col-md-12">';

        foreach ($events as $event) {
            $dateStart = ($event->start->getDate() != '') ? $event->start->getDate() : $event->start->getDateTime();
            $dateEnd = ($event->end->getDate() != '') ? $event->end->getDate() : $event->end->getDateTime();
            $start = new \DateTime($dateStart);
            $end = new \DateTime($dateEnd);
            // TODO: Full day should be handled differently (Shows always next day)
            if ($event->start->getDate() != '' && $event->end->getDate() != '') {
                $end->modify('-1 days');
            }
            $startTime = ($start->format($hourFormat)!='00:00') ? $start->format($hourFormat) : '';
            $endTime = ($end->format($hourFormat)!='00:00') ? $end->format($hourFormat) : '';
            $endFormat = ($start->format($dateFormat) != $end->format($dateFormat)) ?
                $end->format($dateFormat).' '.$endTime : $endTime;
            $startFormat = $start->format($dateFormat).' '.$startTime;
            $fromTo = ($endFormat=='') ? $startFormat : $startFormat.$iconArrowRight. $endFormat;

            $responseContent .= '<div class="row '.$eventClass.'">';
            $responseContent .= '<div class="col-md-12"><'.$hs.'>'.$event->summary.'</'.$hs.'></div>'.
                                '</div>'.
                                '<div class="row">'.
                                '<div class="col-md-12"><'.$hs.'>'.$fromTo.'</'.$hs.'></div>'.
                                '</div>';
            $responseContent .= '<div class="row">';
            if (property_exists($event, 'attendees')) {
                $attendees = "";
                foreach ($event->attendees as $attendee) {
                    $attendeeName = explode("@", $attendee->email);
                    $attendees.= '<b>'.$attendeeName[0].'</b>, ';
                }
                //$body = str_replace("{{attendees}}", $attendees, $body);
                $responseContent .= '<div class="col-md-6">'.$attendees.'</div>';
            }

            if (property_exists($event, 'location')) {
                $responseContent .= '<div class="col-md-6"><'.$hs.'>'.$event->location.'</'.$hs.'></div>';
            }
            $responseContent.= '</div>';
        }

        $responseContent .= "</div></div>";
        // Return the composed HTML
        $response = new Response();
        $response->setContent($responseContent);
        return $response;
    }

    /**
     * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
     * Calendar service JSON
     * @Route("/google_calendar/{int_api_id}", name="json_google_calendar")
     */
    public function json_google_calendar(
        $int_api_id, IntegrationApiRepository $intApiRepository, Request $request, \Google_Client $googleClient)
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
        $googleClientService->setCredentials($_ENV['OAUTH_GOOGLE_CALENDAR_CREDENTIALS']);
        $service = new \Google_Service_Calendar($googleClientService->getClient());
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 2,
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
     * render_timetree internally called. This reads the API data and responds with an HTML content part
     * @Route("/render/timetree", name="render_timetree")
     */
    public function render_timetree(TemplatePartial $partial, IntegrationApiRepository $intApiRepository, SimpleCacheService $cacheService, UserApiRepository $userApiRepository)
    {
        $int_api_id = $partial->getIntegrationApi()->getId();
        $jsonRequest = $this->json_timetree($int_api_id, 'intapi', $partial->getIntegrationApi()->getCalId(),
            $intApiRepository, $userApiRepository);
        $json = json_decode($jsonRequest->getContent());
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createNotFoundException("Parsing of int_api $int_api_id JSON failed: " . json_last_error());
        }
        $user = $partial->getScreen()->getUser();
        $dateFormat = $user->getDateFormat();
        $hourFormat = $user->getHourFormat();

        $responseContent = '';
        // Start HTML building - Headlines is a try to mould this to Screen environment
        $hs = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h3':'h2';
        $colorClass = ($partial->getInvertedColor())?'inverted_color':'default_color';
        $eventClass = ($partial->getInvertedColor())?'default_color':'inverted_color';
        $iconArrowRight = '<span class="glyphicon glyphicon-arrow-right"></span>';
        $iconLogo = '<img src="/assets/screen/logo/timetree-'.$colorClass.'.png"> ';

        $responseContent = '<div class="row '.$colorClass.'"><div class="col-md-12">';

        foreach ($json->data as $item) {
            $attr = $item->attributes;
            $isAllDay = $attr->all_day;
            $start = new \DateTime($attr->start_at, new \DateTimeZone($partial->getIntegrationApi()->getTimezone()));
            // For some reason setting timezone still returns events dated one hour before
            $start->add(new \DateInterval('PT1H'));
            $end = new \DateTime($attr->end_at);
            $end->add(new \DateInterval('PT1H'));

            $responseContent .= '<div class="row '.$eventClass.'">';
            $responseContent .= '<div class="col-md-12"><'.$hs.'>'.$iconLogo.$attr->title.'</'.$hs.'></div>'.
                                '</div><div class="row">'.
                                '<div class="col-md-6"><'.$hs.'>'.$attr->location.'</'.$hs.'></div>';

            if ($isAllDay) {
                $fromTo = $start->format($dateFormat);
            } else {
                $startTime = ($start->format($hourFormat)!='00:00') ? $start->format($hourFormat) : '';
                $endTime = ($end->format($hourFormat)!='00:00') ? $end->format($hourFormat) : '';
                $endFormat = ($start->format($dateFormat) != $end->format($dateFormat)) ?
                    $end->format($dateFormat).' '.$endTime : $endTime;
                $startFormat = $start->format($dateFormat).' '.$startTime;
                $fromTo = ($endFormat=='') ? $startFormat : $startFormat.' '.$iconArrowRight. $endFormat;
            }
            $responseContent .= '<div class="col-md-6"><'.$hs.'>'.$fromTo.'</'.$hs.'></div>';
            $responseContent .= '</div>';
        }

        $responseContent .= "</div></div>";
        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($responseContent);
        return $response;
    }

    /**
     * Darksky renderer internally called
     * @Route("/render/weather", name="render_weather_generic")
     */
    public function render_weather_generic(TemplatePartial $partial, IntegrationApiRepository $intApiRepository, SimpleCacheService $cacheService)
    {
        $int_api_id = $partial->getIntegrationApi()->getId();
        $jsonRequest = $this->json_weather_generic($int_api_id, $intApiRepository, $cacheService);

        $json = json_decode($jsonRequest->getContent());
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createNotFoundException("Parsing of int_api $int_api_id JSON failed: ".json_last_error());
        }
        // Read user preferences
        $user = $partial->getScreen()->getUser();
        $hourFormat = $user->getHourFormat();
        // WEATHER Dark sky
        $celsius = '°C ';
        $d = [];
        $d['summary'] = $json->daily->summary;
        $wIcon = '<i class="wi wi-{icon}"></i>';
        $iconSunrise = str_replace("{icon}", 'day-sunny', $wIcon);
        $d['sunrise'] = $this->convertDateTime($json->daily->data[0]->sunriseTime,$hourFormat);
        $d['sunset'] = $this->convertDateTime($json->daily->data[0]->sunsetTime,$hourFormat);
        $d['daily-avg-high'] = round($json->daily->data[0]->temperatureHigh,1).$celsius;
        $d['daily-avg-low'] =  round($json->daily->data[0]->temperatureLow,1).$celsius;

        $wHourly ="";
        $hourlyCounter = 1;
        // Start HTML building - Headlines is a try to mould this to Screen environment
        $hs = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h3':'h2';
        $colorClass = ($partial->getInvertedColor())?'inverted_color':'default_color';
        $responseContent = '<div class="row '.$colorClass.'"><div class="col-md-12">';
        $responseContent .= "<div class=\"row\">
            <div class=\"col-md-6\"><$hs>Low&nbsp; {$d['daily-avg-low']}<br>High {$d['daily-avg-high']}</$hs></div>
            <div class=\"col-md-6 text-right\"><$hs>$iconSunrise {$d['sunrise']}<br>Sunset&nbsp; {$d['sunset']}</$hs></div></div>";

        // Useless craps: style="margin-top:0.55em"

        $iconCelsius = str_replace("{icon}", 'celsius', $wIcon);
        $icon3 = str_replace("{icon}", 'humidity', $wIcon);
        foreach ($json->hourly->data as $h) {
            $icon1= str_replace("{icon}", $h->icon, $wIcon);
            $temp = strstr(round($h->temperature,1),'.')===false ? round($h->temperature,1).'.0' : round($h->temperature,1);
            $wHourly .= '<div class="row">';
            $wHourly .= '<div class="col-md-4"><'.$hs.'>'.$this->convertDateTime($h->time,$hourFormat).' '.$icon1.'</'.$hs.'></div>'.
                '<div class="col-md-4 text-center"><'.$hs.'>'.$temp.$celsius.'</'.$hs.'></div>'.
                '<div class="col-md-4 text-right"><'.$hs.'>'.($h->humidity*100).' '.$icon3.'</'.$hs.'></div>'; // .$icon3.$h->windSpeed
            $wHourly .= '</div>';
            $hourlyCounter++;
            if ($hourlyCounter>$partial->getMaxResults()) break;
        }
        $responseContent.= $wHourly;
        $responseContent.='<!-- Required by https://darksky.net/dev/docs please do not take out if you use the free version -->
        <div class="row text-right"><small><a href="https://darksky.net/poweredby">Powered by Dark Sky</a></small>&nbsp;</div>';
        $responseContent .= "</div></div>";
        // Return the composed HTML
        $response = new Response();
        $response->setContent($responseContent);
        return $response;
    }

    /**
     * Darksky
     * @Route("/weather/{int_api_id?}", name="json_weather_generic")
     */
    public function json_weather_generic($int_api_id, IntegrationApiRepository $intApiRepository, SimpleCacheService $cacheService)
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
        $clientRequest = $cacheService->request('GET', $apiUrl, $options, $int_api_id);

        $response = new JsonResponse();
        if ($clientRequest->getStatusCode() === 200) {
            $response->setContent($clientRequest->getContent());
        } else {
            $response->setContent(json_encode(['status' => $clientRequest->getStatusCode(),'message' => 'API rest call failed']));
        }
        return $response;
    }

    /**
     * General shared calendar json
     * @Route("/shared-calendar/{api_id?}/{type?userapi}/{cal_id?}", name="json_shared_calendar")
     */
    public function json_timetree($api_id, $type, $cal_id, IntegrationApiRepository $intApiRepository,
                                  UserApiRepository $userApiRepository)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }

        switch($type) {
            case 'userapi':
                $userApi = $userApiRepository->findOneBy(['uuid'=>$api_id]);
                if ($userApi instanceof UserApi === false) {
                    return $this->createNotFoundException("User API not found with ID $api_id");
                }
                $api = $userApi->getApi();
                $apiUrl = $api->getUrl();
                break;
            case 'intapi':
                $intApi = $intApiRepository->findOneBy(['uuid'=>$api_id]);
                if ($intApi instanceof IntegrationApi === false) {
                    return $this->createNotFoundException("Integrated API not found with ID $api_id");
                }
                $userApi = $intApi->getUserApi();
                $api = $userApi->getApi();
                $apiUrl = $api->getUrl();

                if (!is_null($cal_id) && $cal_id!="") {
                    $apiUrl.="/{$cal_id}/upcoming_events";
                }
                if ($intApi->getJsonSettings() !== '') {
                    try {
                        $extraParams = json_decode($intApi->getJsonSettings(), true);
                    } catch (\Exception $e) {
                        return $this->createNotFoundException("Failed parsing json settings for API. ".$e->getMessage());
                    }
                    $apiUrl.= '?'.http_build_query($extraParams);
                }
                break;
        }
        $client = HttpClient::create([
            'auth_bearer' => $userApi->getAccessToken(),
        ]);
        // HTTP Bearer authentication (also called token authentication)
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