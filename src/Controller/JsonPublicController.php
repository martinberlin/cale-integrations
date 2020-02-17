<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\TemplatePartial;
use App\Entity\UserApi;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use App\Service\SimpleCacheService;
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
class JsonPublicController extends AbstractController
{
    // TODO: Maybe would be the best to set Date / Hour format times in user->profile
    private function convertDateTime($unixTime) {
        $dt = new \DateTime("@$unixTime");
        return $dt->format("H:i");
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

        // WEATHER Dark sky copy from raw PHP
        $celsius = '°C ';

        $d = [];
        $dailyIcon = $json->daily->icon;
        $d['summary'] = $json->daily->summary;

        // TODO: Replace for weather Font
        $wIcon = '<img width="50" height="50" src="../svg/wi-{icon}.svg"> &nbsp;';
        $iconSunrise = str_replace("{icon}", 'day-sunny', $wIcon);
        $iconSunset  = str_replace("{icon}", 'sunset', $wIcon);

        $d['sun-time'] = "Sunrise ".$this->convertDateTime($json->daily->data[0]->sunriseTime).
            " Sunset ".$this->convertDateTime($json->daily->data[0]->sunsetTime);

        $d['daily-avg-high'] = $json->daily->data[0]->temperatureHigh.$celsius;

        $d['daily-avg-low'] =  $json->daily->data[0]->temperatureLow.$celsius;

        $wHourly ="";
        $hourlyCounter = 1;

        /*$icon2= str_replace("{icon}", 'celsius', $wIcon);
        $icon3= str_replace("{icon}", 'humidity', $wIcon);

        foreach ($json->hourly->data as $h) {
            $icon1= str_replace("{icon}", $h->icon, $wIcon);
            $temp = strstr(round($h->temperature,1),'.')===false ? round($h->temperature,1).'.0' : round($h->temperature,1);
            $wHourly .= '<div class="row">';
            $wHourly .= '<div class="col-md-4">'.convertDateTime($h->time).$icon1.'</div>'.
                '<div class="col-md-4" style="margin-top:0.55em">'.$temp.$celsius.'</div>'.
                '<div class="col-md-4">'.($h->humidity*100).$icon3.'</div>'; // .$icon3.$h->windSpeed
            $wHourly .= '</div>';
            $hourlyCounter++;
            if ($hourlyCounter>$partial->getMaxResults()) break;
        }*/

        //$readBaseTemplate = str_replace("{{hourly_rows}}", $wHourly, $readBaseTemplate);
        
        // Here we should render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent(print_r($d,true));
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
    public function apiJsonSharedCalendar($api_id, $type,
                                          $cal_id, IntegrationApiRepository $intApiRepository,
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