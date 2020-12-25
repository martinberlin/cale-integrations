<?php
namespace App\Controller\Render;

use App\Entity\Display;
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

use ICal\ICal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
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
    /**
     * @param TemplatePartial $partial
     * @return string
     */
    private function getColorStyle(TemplatePartial $partial, $invert = false) {
        $fColor = ($partial->getInvertedColor()===false) ? $partial->getForegroundColor() : $partial->getBackgroundColor();
        $bColor = ($partial->getInvertedColor()===false) ? $partial->getBackgroundColor() : $partial->getForegroundColor();
        if ($invert === false) {
        $colorStyle = ' style="color:'.$fColor.';background-color:'.$bColor.'"';
        } else {
            $colorStyle = ' style="background-color:'.$fColor.';color:'.$bColor.'"';
        }
        return $colorStyle;
    }
    private function getColorStyleNoWrap(TemplatePartial $partial, $invert = false) {
        $fColor = ($partial->getInvertedColor()===false) ? $partial->getForegroundColor() : $partial->getBackgroundColor();
        $bColor = ($partial->getInvertedColor()===false) ? $partial->getBackgroundColor() : $partial->getForegroundColor();
        if ($invert === false) {
            $colorStyle = "color:$fColor;background-color:$bColor";
        } else {
            $colorStyle = "background-color:$fColor;color:$bColor";
        }
        return $colorStyle;
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
        $response = new Response();
        $responseContent = '';
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
            return $this->createNotFoundException("Integrated API not found for partial: " . $partial->getId());
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
        try {
            $results = $service->events->listEvents($calendarId, $optParams);
        } catch (\Google_Service_Exception $e) {
            $exceptionObj = json_decode($e->getMessage());
            $log->setMessage('Google_Service exception: ' . $exceptionObj->error);
            $em->persist($log);
            $em->flush();
            $responseContent .= '<h3>' . $exceptionObj->error . '</h3>';
            $responseContent .= "Error with Google Auth token. Please get a new token in Content section: Google Calendar";
            $response->setContent($responseContent);
            return $response;
        }

        $events = $results->getItems();
        // Start HTML building - Headlines is a try to mould this to Screen environment
        $hs = (substr($partial->getScreen()->getTemplateTwig(), 0, 1) > 1) ? 'h4' : 'h3';
        // Retrieve color styles
        $mainColorStyle = $this->getColorStyle($partial);
        $invertedColorStyle = $this->getColorStyle($partial, true);
        $iconArrowRight = ' <span class="glyphicon glyphicon-arrow-right"></span>';
        $responseContent = '<div class="row" style="margin-left:0px;'.$mainColorStyle.'"><div class="col-md-12">';

        foreach ($events as $event) {
            $dateStart = ($event->start->getDate() != '') ? $event->start->getDate() : $event->start->getDateTime();
            $dateEnd = ($event->end->getDate() != '') ? $event->end->getDate() : $event->end->getDateTime();
            $start = new \DateTime($dateStart);
            $end = new \DateTime($dateEnd);
            // TODO: Full day should be handled differently (Shows always next day)
            if ($event->start->getDate() != '' && $event->end->getDate() != '') {
                $end->modify('-1 days');
            }
            $startTime = ($start->format($hourFormat) != '00:00') ? $start->format($hourFormat) : '';
            $endTime = ($end->format($hourFormat) != '00:00') ? $end->format($hourFormat) : '';
            $endFormat = ($start->format($dateFormat) != $end->format($dateFormat)) ?
                $end->format($dateFormat) . ' ' . $endTime : $endTime;
            $startFormat = $start->format($dateFormat) . ' ' . $startTime;
            $fromTo = ($endFormat == '') ? $startFormat : $startFormat . $iconArrowRight . $endFormat;

            $responseContent .= '<div class="row"' . $invertedColorStyle . '>';
            $responseContent .= '<div class="col-md-12"><' . $hs . '>' . $event->summary . '</' . $hs . '></div>' .
                '</div>' .
                '<div class="row">' .
                '<div class="col-md-12"><' . $hs . '>' . $fromTo . '</' . $hs . '></div>' .
                '</div>';
            $responseContent .= '<div class="row">';
            if (property_exists($event, 'attendees')) {
                $attendees = "";
                foreach ($event->attendees as $attendee) {
                    $attendeeName = explode("@", $attendee->email);
                    $attendees .= '<b>' . $attendeeName[0] . '</b>, ';
                }
                //$body = str_replace("{{attendees}}", $attendees, $body);
                $responseContent .= '<div class="col-md-6 col-sm-6 col-xs-6">' . $attendees . '</div>';
            }

            if (property_exists($event, 'location')) {
                $responseContent .= '<div class="col-md-6 col-sm-6 col-xs-6"><' . $hs . '>' . $event->location . '</' . $hs . '></div>';
            }
            $responseContent .= '</div>';
        }

        $responseContent .= "</div></div>";
        // Return the composed HTML
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
        $googleClient = $googleClientService->getClient();

        if ($googleClient instanceof \Google_Client) {
            $service = new \Google_Service_Calendar($googleClient);
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
        } else {
            // Google client replies with an HttpFundation Response
            $response = $googleClient;
        }
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
        $hs = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h4':'h3';
        $mainColorStyle = $this->getColorStyleNoWrap($partial);
        $colorStyle = $this->getColorStyle($partial);
        $invertedColorStyle = $this->getColorStyle($partial, true);

        $iconArrowRight = '<span class="glyphicon glyphicon-arrow-right"></span>';
        $iconLogo = '<img src="/assets/screen/logo/timetree-default_color.png"> ';

        $responseContent = '<div class="row" style="margin-left:0px;'.$mainColorStyle.'"><div class="col-md-12">';
        if (isset($json->data)) {
            $countResults = 1;
            foreach ($json->data as $item) {
                if ($countResults>$partial->getMaxResults()) {
                    break;
                }
                $attr = $item->attributes;
                $isAllDay = $attr->all_day;
                $start = new \DateTime($attr->start_at, new \DateTimeZone($partial->getIntegrationApi()->getTimezone()));
                // For some reason setting timezone still returns events dated one hour before
                $start->add(new \DateInterval('PT1H'));
                $end = new \DateTime($attr->end_at);
                $end->add(new \DateInterval('PT1H'));

                $responseContent .= '<div class="row"'.$invertedColorStyle.'>';

                $responseContent .= '<div class="col-md-12"><'.$hs.'>'.$iconLogo.$attr->title.'</'.$hs.'></div>'.
                                    '</div><div class="row">';

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
                $responseContent .= '<div class="col-md-8 col-sm-6 col-xs-6"><'.$hs.'>'.$fromTo.'</'.$hs.'></div>';
                $responseContent .= '<div class="col-md-4 col-sm-6 col-xs-6"><'.$hs.'>'.$attr->location.'</'.$hs.'></div>';

                $responseContent .= '</div>';

                $countResults++;
            }
        } else {
            $responseContent .= '<b>NO DATA FROM API: There was no data response from Timetree.</b><br>Please check in Content that the API has a Token configured';
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
        $response = new Response();
        $json = json_decode($jsonRequest->getContent());
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createNotFoundException("Parsing of int_api $int_api_id JSON failed: ".json_last_error());
        }
        if (property_exists($json, 'error')) {
            $response->setContent('<h3>'.$json->error.'</h3> Please delete the weather API and try to reconfigure again using a correct KEY');
            return $response;
        }
        // Read user preferences
        $colorStyle = $this->getColorStyleNoWrap($partial);
        $user = $partial->getScreen()->getUser();
        $hourFormat = $user->getHourFormat();
        // WEATHER Dark sky
        $units = ($partial->getIntegrationApi()->getUnits() === 'imperial') ? 'F° ':'C° ';
        $d = [];
        $d['summary'] = $json->daily->summary;
        $wIcon = '<i class="wi wi-{icon}"></i>';
        $iconSunrise = str_replace("{icon}", 'day-sunny', $wIcon);
        $d['sunrise'] = $this->convertDateTime($json->daily->data[0]->sunriseTime,$hourFormat);
        $d['sunset'] = $this->convertDateTime($json->daily->data[0]->sunsetTime,$hourFormat);
        $d['daily-avg-high'] = round($json->daily->data[0]->temperatureHigh,1).$units;
        $d['daily-avg-low'] =  round($json->daily->data[0]->temperatureLow,1).$units;

        $wHourly ="";
        $hourlyCounter = 1;
        // Start HTML building - Headlines is a try to mould this to Screen environment
        $hs = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h4':'h3';
        $colMd6 = ($partial->getScreen()->getDisplay() instanceof Display && $partial->getScreen()->getDisplay()->getWidth()>400) ? 'col-md-6 col-sm-6' : 'col-xs-6';
        $colMd4 = ($partial->getScreen()->getDisplay() instanceof Display && $partial->getScreen()->getDisplay()->getWidth()>400) ? 'col-md-4 col-sm-4' : 'col-xs-4';

        $responseContent = '<div class="row" style="margin:0px;padding-top:6px;'.$colorStyle.'"><div class="col-md-12 col-sm-12 col-xs-12">';
        $responseContent .= "<div class=\"row\">
            <div class=\"$colMd6 col-xs-6 \"><$hs>Low&nbsp; {$d['daily-avg-low']}<br>High {$d['daily-avg-high']}</$hs></div>
            <div class=\"$colMd6 col-xs-6 text-right\"><$hs>$iconSunrise {$d['sunrise']}<br>&nbsp;Sunset {$d['sunset']}</$hs></div></div>";

        $responseContent .= "<div style=\"margin-top:0.5em;margin-bottom:0.5em\" class=\"row\" $colorStyle>
            <div class=\"col-md-12 col-xs-12\"><h4>".$json->daily->summary."</h4></div></div>";

        $icon3 = str_replace("{icon}", 'humidity', $wIcon);
        foreach ($json->hourly->data as $h) {
            $icon1= str_replace("{icon}", $h->icon, $wIcon);
            $temp = strstr(round($h->temperature,1),'.')===false ? round($h->temperature,1).'.0' : round($h->temperature,1);
            $wHourly .= '<div class="row">';

            if ($partial->getScreen()->getDisplay() instanceof Display && $partial->getScreen()->getDisplay()->getWidth()>400) {
                $wHourly .= '<div class="'.$colMd4.'"><'.$hs.'>'.$this->convertDateTime($h->time,$hourFormat).' '.$icon1.' </'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-center"><'.$hs.'>'.$temp.$units.'</'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-right"><'.$hs.'>'.($h->humidity*100).' '.$icon3.'</'.$hs.'></div>';
            } else {
                $wHourly .= '<div class="'.$colMd4.'"><'.$hs.'>'.$this->convertDateTime($h->time,$hourFormat).' '.$icon1.' </'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-center" style="margin-left:1.4em"><'.$hs.'>'.$temp.$units.'</'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-right" style="margin-left:1.4em"><'.$hs.'>'.($h->humidity*100).' '.$icon3.'</'.$hs.'></div>';
            }
                $wHourly .= '</div>';
            $hourlyCounter++;
            if ($hourlyCounter>$partial->getMaxResults()) break;
        }
        $responseContent.= $wHourly;
        //$responseContent.='<div class="row text-right"><small><a href="https://darksky.net/poweredby" class="partial-link">Powered by Dark Sky</a></small></div>';
        $responseContent .= "</div></div>";

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

            switch ($api->getUrlName()) {
                case 'weather-darksky':
                    $extraParams['units'] = ($intApi->getUnits()==='imperial') ? 'us' : 'si';
                    $apiUrl.= '?'.http_build_query($extraParams);
                    break;
                default:
                    $extraParams['units'] = $intApi->getUnits();
                    $apiUrl.= '&'.http_build_query($extraParams);
            }
        }
        $response = new JsonResponse();
        try {
            $clientRequest = $cacheService->request('GET', $apiUrl, $options, $int_api_id);
        } catch (ClientException $e) {
            $response->setContent(json_encode(['error' => 'API rest call failed. Not authorized, bad key?', 'message' => $e->getMessage()]));
            return $response;
        }

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

    /**
     * iCal renderer
     * @Route("/render_int_ical", name="render_int_ical")
     */
    public function render_int_ical(TemplatePartial $partial, IntegrationApiRepository $intApiRepository)
    {
        $api = $partial->getIntegrationApi()->getUserApi();
        // Read user preferences
        $user = $partial->getScreen()->getUser();

        $error = "";
        try {
            $ical = new ICal();
            $ical->initUrl($api->getResourceUrl(),
                $username = $api->getUsername(), $password = $api->getPassword(),
                $userAgent = null);
            $events = $ical->eventsFromInterval('1 week');
        } catch (\Exception $e) {
            $error = "Could not access iCal. ".$e->getMessage();
        }
        $colorStyle = $this->getColorStyle($partial);

        $hs1 = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h4':'h3';
        $hs2 = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h5':'h4';
        $colMd = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'col-md-6 col-sm-6':'col-md-12 col-sm-12';

        $html = $error.' <div class="row"'.$colorStyle.'>';
        $count = 1;

        foreach ($events as $event) {
            if ($count>$partial->getMaxResults()) break;
            $dateStart = ($ical->iCalDateToDateTime($event->dtstart));
            $dateEnd = ($ical->iCalDateToDateTime($event->dtend));

            $dtstart = $ical->iCalDateToDateTime($event->dtstart_array[3]);
            $summary = $event->summary . ' - '.$dtstart->format($user->getDateFormat());
            $html .= '<div class="'.$colMd.'">
                        <'.$hs1.'>'.$summary."</$hs1>";
            $html .= "<$hs2>". $dateStart->format($user->getHourFormat())." to ".$dateEnd->format($user->getHourFormat())."</$hs1>
                      </div>";
            $count++;
        }
        $html.= '</div>';

        $response = new Response();
        $response->setContent($html);
        return $response;
    }

    private function convertDateTime($unixTime, $hourFormat) {
        $dt = new \DateTime("@$unixTime");
        return $dt->format($hourFormat);
    }

    /**
     * @param $unixTime
     * @param $hourFormat
     * @return string
     * For openWeather comes already a timezone shift from UTC
     */
    private function convertDateTimeUTC($unixTime, $hourFormat) {
        $dt = new \DateTime("@$unixTime");
        $dt->setTimezone(new \DateTimeZone("UTC"));
        return $dt->format($hourFormat);
    }

    /**
     * OpenWeather
     * @Route("/render/openweather", name="render_openweather")
     */
    public function render_openweather(TemplatePartial $partial, IntegrationApiRepository $intApiRepository, SimpleCacheService $cacheService)
    {
        $int_api_id = $partial->getIntegrationApi()->getId();
        $jsonRequest = $this->json_weather_generic($int_api_id, $intApiRepository, $cacheService);
        $response = new Response();
        $json = json_decode($jsonRequest->getContent());
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createNotFoundException("Parsing of int_api $int_api_id JSON failed: ".json_last_error());
        }
        if (property_exists($json, 'error')) {
            $response->setContent('<h3>'.$json->error.'</h3> Please delete the weather API and try to reconfigure again using a correct KEY');
            return $response;
        }
        // Read user preferences
        $colorStyle = $this->getColorStyleNoWrap($partial);
        $user = $partial->getScreen()->getUser();
        $hourFormat = $user->getHourFormat();
        $units = ($partial->getIntegrationApi()->getUnits() === 'imperial') ? ' F°':'°';

        $hIcon = '<i class="wi wi-{icon}"></i>';
        $iconColor = ($partial->getInvertedColor()) ? 'w/' : '';
        $wIcon = '<img style="width:1.6em" src="/assets/svg/openweather/'.$iconColor.'{icon}.svg">';
        $wHourly ="";
        $hourlyCounter = 1;
        // Find timezone Shift in seconds from UTC and city name
        $cityName = "";
        $timezoneCorrection = 0;
        if (isset($json->city)) {
            // Minus one hour since seems 3600 seconds in the future (Not sure if this is right)  -3600
            $timezoneCorrection = $json->city->timezone - 3600;
            $cityName = $json->city->name;
        }
        // Start HTML building - Headlines is a try to mould this to Screen environment. Ref: https://openweathermap.org/current
        $hs = (substr($partial->getScreen()->getTemplateTwig(),0,1)>1)?'h4':'h3';
        $colMd4 = ($partial->getScreen()->getDisplay() instanceof Display && $partial->getScreen()->getDisplay()->getWidth()>400) ? 'col-md-4 col-sm-4' : 'col-xs-4';

        $responseContent = '<div class="row" style="margin:0px;padding-top:6px;'.$colorStyle.'">
                                <div class="col-md-12 col-sm-12 col-xs-12"><h4>'.$cityName.'</h4>';

        $icon3 = str_replace("{icon}", 'humidity', $hIcon);

        foreach ($json->list as $h) {
            $icon1= str_replace("{icon}", $h->weather[0]->icon, $wIcon);
            $temp = strstr(round($h->main->temp,1),'.')===false ? round($h->main->temp,1).'.0' : round($h->main->temp,1);
            $wHourly .= '<div class="row">';

            if ($partial->getScreen()->getDisplay() instanceof Display && $partial->getScreen()->getDisplay()->getWidth()>400) {
                $wHourly .= '<div class="'.$colMd4.'"><'.$hs.'>'.$this->convertDateTimeUTC($h->dt+$timezoneCorrection,$hourFormat).' '.$icon1.' </'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-center"><'.$hs.'>'.$temp.$units.'</'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-right"><'.$hs.'>'.$h->main->humidity.' '.$icon3.'</'.$hs.'></div>';
            } else {
                $wHourly .= '<div class="'.$colMd4.'"><'.$hs.'>'.$this->convertDateTimeUTC($h->dt+$timezoneCorrection,$hourFormat).' '.$icon1.' </'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-center" style="margin-left:1.4em"><'.$hs.'>'.$temp.$units.'</'.$hs.'></div>';
                $wHourly .= '<div class="'.$colMd4.' text-right" style="margin-left:1.4em"><'.$hs.'>'.$h->main->humidity.' '.$icon3.'</'.$hs.'></div>';
            }
            $wHourly .= '</div>';
            $hourlyCounter++;
            if ($hourlyCounter>$partial->getMaxResults()) break;
        }
        $responseContent.= $wHourly;
        //$responseContent.='<div class="row text-right"><small><a href="https://darksky.net/poweredby" class="partial-link">Powered by Dark Sky</a></small></div>';
        $responseContent .= "</div></div>";

        $response->setContent($responseContent);
        return $response;
    }
}