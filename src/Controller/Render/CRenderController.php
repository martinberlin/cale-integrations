<?php
namespace App\Controller\Render;

use App\Entity\IntegrationApi;
use App\Entity\TemplatePartial;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use App\Repository\UserRepository;
use App\Service\SimpleCacheService;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to render AWS Cloudwatch PNG images
 * @Route("/aws/cloudwatch")
 */
class CRenderController extends AbstractController
{
    /**
     * render_preview is called in step2 of API config after setting the settingsJson
     * @Route("/render_preview/{username}/{intapi}", name="render_aws_cloudwatch_preview")
     */
    public function render_preview($username, $intapi, IntegrationApiRepository $intApiRepository, UserRepository $userRepository)
    {
        $api = $intApiRepository->findOneBy(['uuid' => $intapi]);
        $user = $userRepository->findOneBy(['name' => $username]);
        if (!$api instanceof IntegrationApi) {
            throw $this->createNotFoundException('API not found');
        }
        $userApi = $api->getUserApi();
        $imageJson = $api->getJsonSettings();
        if ($imageJson == "" || is_null($imageJson)) {
            throw $this->createNotFoundException("imageJson definition can't be empty");
        }

        if ($userApi->getUser() !== $user || $userApi->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("This user is not the owner of this API");
        }

        $requestOptions = [];
        if ($_ENV['AWS_PROXY']) {
            $requestOptions = [
                'proxy' => 'https://'.$_ENV['AWS_PROXY']
            ];
        }
        $client = new CloudWatchClient([
            'region' => $userApi->getRegion(),
            'version' => '2010-08-01',
            'credentials' => [
                'key' => $userApi->getUsername(), 'secret' => $userApi->getPassword()
            ],
            'request.options' => $requestOptions
        ]);

        $response = new Response();
        try {
            $result = $client->getMetricWidgetImage([
                'MetricWidget' => $imageJson
            ]);
        } catch (AwsException $e) {
            // output error message if fails
            $response->setContent($e->getMessage());
            return $response;
        }

        if ($result instanceof \Aws\Result) {
            $image = $result->get('MetricWidgetImage');

            $response->setContent($image);
            $response->headers->set('Content-Type', 'image/png');
            return $response;
        }
    }
}