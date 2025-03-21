<?php
namespace App\Controller;

use App\Entity\ApiLog;
use App\Entity\IntegrationApi;
use App\Entity\User;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 * Public API to log SCD40 & similar sensors data
 */
class ApiLogController extends AbstractController {

    /**
     * @Route("/scd40/log", name="api_scd40_log", methods={"POST"})
     */
    public function log(Request $request, UserRepository $userRepository , IntegrationApiRepository $intApiRepository): Response {
        try {
            $parsed = json_decode($request->getContent(), $associative=true, $depth=512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        $client = $parsed['client'];
        $userId = $client['id'];
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (! $user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }
        $api = $intApiRepository->findOneBy(['uuid' => $client['key']]);
        if (! $api instanceof IntegrationApi) {
            throw new NotFoundHttpException("API with key {$client['key']} not found");
        }

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
        $apiLog->setDatestamp(new \DateTime());
        // Set IntegrationApi UUID: Now set in the constructor

        try {
            $em->persist($apiLog);
            $em->flush();
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Error saving data. ERROR: ' . $e->getMessage());
        }

        $response = new JsonResponse();
        $response->setContent(json_encode([
            'status' => 'ok',
            'data' => $parsed,
            ])
        );
        return $response;
    }

    /**
     * @Route("/scd40/read/{key}/{length?100}", name="api_scd40_read")
     */
    public function logRead($key, $length, Request $request, IntegrationApiRepository $intApiRepository): Response {
        $api = $intApiRepository->findOneBy(['uuid' => $key]);
        if (! $api instanceof IntegrationApi) {
            throw new NotFoundHttpException("API with key {$key} not found");
        }

        $length = (int) $length;
        $length = $length > 1000 ? 1000 : $length;
        $length = $length < 1 ? 1 : $length;
        $em = $this->getDoctrine()->getManager();
        $apiLogs = $em->getRepository(ApiLog::class)->findBy([], ['timestamp' => 'DESC'], $length);
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
}