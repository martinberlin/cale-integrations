<?php

namespace App\Controller;

use App\Entity\ApiLog;
use App\Entity\ApiLogAmpere;
use App\Entity\IntegrationApi;
use App\Entity\User;

use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiAmpereSettingsRepository;
use App\Repository\UserApiLogChartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/api")
 * Public API to log SCD40 & similar sensors data
 */
class ApiLogController extends AbstractController
{

    /**
     * @Route("/scd40/log", name="api_scd40_log", methods={"POST"})
     */
    public function log(Request                   $request, UserRepository $userRepository, IntegrationApiRepository $intApiRepository,
                        UserApiLogChartRepository $apiLogChartRepository, HttpClientInterface $httpClient): Response
    {
        try {
            $parsed = json_decode($request->getContent(), $associative = true, $depth = 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        $client = $parsed['client'];
        $userId = $client['id'];
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }
        $api = $intApiRepository->findOneBy(['uuid' => $client['key']]);
        if (!$api instanceof IntegrationApi) {
            throw new NotFoundHttpException("API with key {$client['key']} not found");
        }
        $apiConfig = $apiLogChartRepository->findOneBy(['intApi' => $api]);

        if ($parsed['temperature'] <= -40) {
            throw new NotFoundHttpException('Invalid sensor data');
        }
        // Log this into the database
        $em = $this->getDoctrine()->getManager();
        $apiLog = new ApiLog();
        $apiLog->setUser($user);
        $apiLog->setApi($api);
        $apiLog->setTemperature($parsed['temperature']);
        $apiLog->setHumidity($parsed['humidity']);
        $apiLog->setCo2($parsed['co2']);
        $apiLog->setTimezone($client['timezone']);

        if (isset($parsed['light'])) {
            $apiLog->setLight($parsed['light']);
        }
        if (isset($client['timestamp'])) {
            $apiLog->setTimestamp(new \DateTime($parsed['timestamp']));
        }
        $apiLog->setDatestamp(new \DateTime());
        if (isset($client['ip'])) {
            $apiLog->setClientIp($client['ip']);
        }

        $response = new JsonResponse();
        $validValues = ['temperature', 'humidity', 'co2', 'light'];
        if ($apiConfig->getTelemetryActive()) {
            if (!in_array($apiConfig->getTelemetryCargo(), $validValues)) {
                $response->setContent(json_encode([
                        'status' => 'error',
                        'message' => 'Invalid telemetry cargo'
                    ])
                );
                return $response;
            }
            $payload = new StdClass();
            $payload->ship_id = $apiConfig->getTelemetryDevice();
            $payload->cargo_id = $apiConfig->getTelemetryCargo();
            $payload->value = $parsed[$apiConfig->getTelemetryCargo()];
            //$payload->time = $apiLog->getDatestamp()->format('Y-m-d\TH:i:s\Z');
            $payload->time =  date("c", $apiLog->getDatestamp()->getTimestamp());
            $payloadJson = json_encode($payload);
            //exit($payloadJson);
            // Make an additional call to the telemetry API
            // $apiConfig->getTelemetryIngestUrl() != ''
            $request1Status = 'no request';
            $request2Status = 'no request';
            if ($apiConfig->getTelemetryIngestUrl() != '') {
                try {
                    $request1 = $httpClient->request(
                        'POST',
                        $apiConfig->getTelemetryIngestUrl(),
                        [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'X-API-Key' => $apiConfig->getTelemetryApiKey(),
                            ],
                            'body' => $payloadJson
                        ]
                    );
                } catch (ClientException $e) {
                    $response->setContent(json_encode([
                            'status' => 'error',
                            'message' => $e->getMessage()
                        ])
                    );
                    return $response;
                }
                $request1Status = $request1->getStatusCode();
            }


            if ($apiConfig->getTelemetryIngestUrl2() != '') {
                if (!in_array($apiConfig->getTelemetryCargo2(), $validValues)) {
                    $response->setContent(json_encode([
                            'status' => 'error',
                            'message' => 'Invalid telemetry cargo2'
                        ])
                    );
                    return $response;
                }
                $payload->ship_id = $apiConfig->getTelemetryDevice2();
                $payload->cargo_id = $apiConfig->getTelemetryCargo2();
                $payload->value = $parsed[$apiConfig->getTelemetryCargo2()];
                $payload->time =  date("c", $apiLog->getDatestamp()->getTimestamp());
                $payloadJson = json_encode($payload);

                sleep(1); // 1 second delay to avoid API restrictions
                try {
                    $request2 = $httpClient->request(
                        'POST',
                        $apiConfig->getTelemetryIngestUrl2(),
                        [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'X-API-Key' => $apiConfig->getTelemetryApiKey2(),
                            ],
                            'body' => $payloadJson
                        ]
                    );
                } catch (ClientException $e) {
                    $response->setContent(json_encode([
                            'status' => 'error',
                            'message' => $e->getMessage()
                        ])
                    );
                    return $response;
                }
                $request2Status = $request2->getStatusCode();
            }

            //dump($request->getStatusCode());exit();
            /**
             * {
             * "ship_id": "example_device",
             * "cargo_id": "temperature",
             * "value": 25.5,
             * "time": "2025-03-26T07:00:08.530Z"
             * }
             */
        }
        // Set IntegrationApi UUID: Now set in the constructor

        try {
            $em->persist($apiLog);
            $em->flush();
            $response->setContent(json_encode([
                    'status' => 'ok',
                    'th_request1' => $request1Status,
                    'th_request2' => $request2Status,
                ])
            );
        } catch (\Exception $e) {
            $response->setContent(json_encode([
                    'status' => 'error',
                    'message' => 'Error saving data. ERROR: ' . $e->getMessage()
                ])
            );
        }
        return $response;
    }

    /**
     * @Route("/scd40/read/{key}/{length?100}", name="api_scd40_read")
     */
    public function logRead($key, $length, Request $request, IntegrationApiRepository $intApiRepository): Response
    {
        $api = $intApiRepository->findOneBy(['uuid' => $key]);
        if (!$api instanceof IntegrationApi) {
            throw new NotFoundHttpException("API with key {$key} not found");
        }

        $length = (int)$length;
        $length = $length > 1000 ? 1000 : $length;
        $length = $length < 1 ? 1 : $length;
        $em = $this->getDoctrine()->getManager();
        $apiLogs = $em->getRepository(ApiLog::class)->findBy(['api' => $api], ['timestamp' => 'DESC'], $length);
        $data = [];
        foreach ($apiLogs as $apiLog) {
            $data[] = [
                'datestamp' => $apiLog->getDatestamp()->format('d/m/y H:i'),
                'temperature' => $apiLog->getTemperature(),
                'humidity' => $apiLog->getHumidity(),
                'co2' => $apiLog->getCo2(),
                'timezone' => $apiLog->getTimezone(),
            ];
        }
        $data = array_reverse($data);
        $response = new JsonResponse();
        $response->setContent(json_encode([
                'data' => $data,
            ])
        );
        return $response;
    }

    /**
     * @Route("/scd40/readtimes/{key}/{length?100}/{times_read?0}", name="api_scd40_read_times")
     */
    public function logReadTimes($key, $length, $times_read, Request $request, IntegrationApiRepository $intApiRepository, EntityManagerInterface $em): Response
    {
        $api = $intApiRepository->findOneBy(['uuid' => $key]);
        if (!$api instanceof IntegrationApi) {
            throw new NotFoundHttpException("API with key {$key} not found");
        }

        $length = (int)$length;
        $length = $length > 1000 ? 1000 : $length;
        $length = $length < 1 ? 1 : $length;

        $apiLogs = $em->getRepository(ApiLog::class)->findBy(['api' => $api, 'timesRead' => $times_read], ['timestamp' => 'ASC'], $length);
        $data = [];
        foreach ($apiLogs as $apiLog) {
            $data[] = [
                'datestamp' => $apiLog->getDatestamp()->format('d/m/y H:i'),
                'temperature' => $apiLog->getTemperature(),
                'humidity' => $apiLog->getHumidity(),
                'co2' => $apiLog->getCo2(),
                'timezone' => $apiLog->getTimezone(),
            ];
            $apiLog->setTimesRead($apiLog->getTimesRead() + 1);
            $em->persist($apiLog);
        }
        $em->flush();
        //$data = array_reverse($data);

        $response = new JsonResponse();
        $response->setContent(json_encode([
                'data' => $data,
            ])
        );
        return $response;
    }

    /**
     * @Route("/energy-consumption/log", name="api_ampere_log", methods={"POST"})
     */
    public function logAmpere(Request $request, UserRepository $userRepository, IntegrationApiRepository $intApiRepository, UserApiAmpereSettingsRepository $userApiAmpereSettingsRepository): Response
    {
        try {
            $parsed = json_decode($request->getContent(), $associative = true, $depth = 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        $client = $parsed['client'];
        $userId = $client['id'];
        $totalWatt = $client['total_wh'];
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }
        $api = $intApiRepository->findOneBy(['uuid' => $client['key']]);
        if (!$api instanceof IntegrationApi) {
            throw new NotFoundHttpException("API with key {$client['key']} not found");
        }

        $apiConfig = $userApiAmpereSettingsRepository->findOneBy(['intApi' => $api]);

        $response = new JsonResponse();
        $status = "ok";
        $errorCnt = 0;
        $errorLast = "";
        $resetCounter = 0;

        foreach ($parsed['data'] as $data) {
            if ($data['v'] <= 0) {
                throw new NotFoundHttpException('Invalid voltage data');
            }
            // Log this into the database
            $em = $this->getDoctrine()->getManager();
            $apiLog = new ApiLogAmpere();
            $apiLog->setUser($user);
            $apiLog->setApi($api);
            $apiLog->setFp($data['f']);
            $apiLog->setTotalWh($totalWatt);
            $apiLog->setVolt($data['v']);
            $apiLog->setWatt($data['p']);
            $apiLog->setHour($data['hr']);
            //dump($data);exit();
            $apiLog->setTimestamp($data['t']);
            $apiLog->setTimezone($apiConfig->getTimezone());
            //Timezone is now in API config  UserApiAmpereSettings
            $dateString = date('Y-m-d H:i:s', $data['t']);
            $apiLog->setDatestamp(new \DateTime($dateString));

            try {
                $em->persist($apiLog);
                $em->flush();

            } catch (\Exception $e) {
                $errorCnt++;
                $errorLast = $e->getMessage();
            }
        }
        if ($apiConfig->getResetCounterDay() == date('d')) {
            $resetCounter = 1;
            $apiConfig->setDatestampLastReset(new \DateTime());
            $em->persist($apiConfig);
            $em->flush();
        }
        if ($errorCnt > 0) {
            $status = "error";
        }
        $response->setContent(json_encode([
                'reset' => $resetCounter,
                'status' => $status,
                'errors' => $errorCnt,
            ])
        );
        return $response;
    }
}