<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Form\Api\ApiConfigureSelectionType;
use App\Form\Api\IntegrationWeatherApiType;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend")
 */
class BackendController extends AbstractController
{
    /**
     * @Route("/", name="b_home")
     */
    public function home()
    {
        return $this->render(
            'backend/admin-home.html.twig',
            [
                'title' => 'Admin dashboard'
            ]
        );
    }

    /**
     * @Route("/users", name="b_users")
     */
    public function users(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $users = $userRepository->findAll();

        return $this->render(
            'backend/admin-users.html.twig',
            [
                'title' => 'List users',
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/api/configure", name="b_api_configure")
     */
    public function apiConfigure(Request $request, EntityManagerInterface $entityManager)
    {
        $userApi = new UserApi();
        $form = $this->createForm(ApiConfigureSelectionType::class, $userApi);
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $userApi->setUser($this->getUser());
            try {
                $entityManager->persist($userApi);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error === '') {
                $this->addFlash('success', 'API is connected with your user');

                // todo: think about smarter way to do this
                if ($userApi->getApi()->isLocationApi()) {
                   return $this->redirectToRoute('b_api_customize_location',
                        [
                         'uuid' => $userApi->getId()
                        ]);
                }
            }
        }

        return $this->render(
            'backend/api/configure-api.html.twig',
            [
                'title' => 'Api configurator. Step 1',
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/api/customize/location/{uuid}", name="b_api_customize_location")
     */
    public function apiCustomizeLocation(
        $uuid, Request $request,UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository, EntityManagerInterface $entityManager)
    {
        $languages = $this->getParameter('api_languages');
        $userApi = $userApiRepository->findOneBy(['uuid'=>$uuid]);
        if (!$userApi instanceof UserApi){
            throw $this->createNotFoundException("$uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()){
            throw $this->createNotFoundException("You don't have access to API $uuid");
        }

        $api = $intApiRepository->findOneBy(['userApi'=>$userApi]);
        if ($api instanceof IntegrationApi === false) {
            $api = new IntegrationApi();
        }
        if ($this->getUser()->getLanguage()!=="") {
          $api->setLanguage($this->getUser()->getLanguage());
        }

        $api->setJsonSettings($userApi->getApi()->getDefaultJsonSettings());

        $form = $this->createForm(IntegrationWeatherApiType::class, $api,
            [
                'languages' => array_flip($languages)
            ]);
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $api->setUserApi($userApi);

            try {
                $entityManager->persist($api);

            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error === '') {
                $userApi->setIsConfigured(true);
                $entityManager->persist($userApi);
                $entityManager->flush();
                $this->addFlash('success', 'Location API configuration saved');
            }
        }

        return $this->render(
            'backend/api/location-api.html.twig',
            [
                'title' => 'Api customize location Api. Step 2',
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/ip_location/json/{type}", name="b_iptolocation")
     */
    public function apiExternalIpToLocation(Request $r, $type)
    {
        $ip = $r->getClientIp();
        if ($ip == '127.0.0.1') {
            $ip = file_get_contents($_ENV['API_IP_ECHO']);
        }
        $locationApi = str_replace("{{IP}}", $ip, $_ENV['API_IP_LOCATION']);

        $client = HttpClient::create();
        $response = $client->request('GET', $locationApi);
        $r = new JsonResponse();

        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            switch ($type) {
                case 'location':
                    $json = json_decode($content);
                    $content = json_encode($json->location);
                    break;
                case 'language':
                    $json = json_decode($content);
                    $content = json_encode($json->location->languages[0]);
                    break;
                case 'geo':
                    $json = json_decode($content);
                    $geo = [
                        'latitude' =>$json->latitude,
                        'longitude'=>$json->longitude,
                        'city'     =>$json->city
                    ];
                    $content = json_encode($geo);
                    break;
            }
            $r->setContent($content);
        } else {
            $r->setContent('{"error":"Location API returned status:".$response->getStatusCode()."}")');
        }

        return $r;
    }
}
