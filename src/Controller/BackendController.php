<?php
namespace App\Controller;

use App\Form\UsernameAgreementType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/backend")
 */
class BackendController extends AbstractController
{
    /**
     * @Route("/logged_in", name="logged_in")
     */
    public function loggedIn(EntityManagerInterface $entityManager, Request $request)
    {
        $user = $this->getUser();
        if (is_null($user->getLastLogin()) || !$user->getAgreementAccepted()) {
            $form = $this->createForm(UsernameAgreementType::class, $user);

            $form->handleRequest($request);
            if ($form->getClickedButton()) {
                switch ($form->getClickedButton()->getName()) {
                    case 'declineAction':
                        $entityManager->remove($user);
                        $entityManager->flush();
                        $this->addFlash('success', "Your account was deleted from our system");
                        return $this->redirectToRoute('home');
                        break;

                    case 'confirmAction':
                        if ($form->get('confirm')->getData()) {
                            $user->setLastLogin(new \DateTime());
                            $user->setAgreementAccepted(true);
                            $error = "";
                            try {
                                $entityManager->persist($user);
                                $entityManager->flush();
                            } catch (UniqueConstraintViolationException $e) {
                                $error = "This username is taken please choose another one";
                            }

                            if ($error === "") {
                                $this->addFlash('success',
                                    "Thanks for accepting our terms. Your account was created with the username: " . $user->getName().
                                ". Please check that your profile is complete and set your Timezone");
                                return $this->redirectToRoute('b_user_profile');
                            } else {
                                $this->addFlash('error', $error);
                            }

                        } else {
                            $this->addFlash('error', "Please accept the agreement to confirm your account");
                        }
                        break;
                }
            }
            return $this->render(
                'backend/admin-accept-agreement.html.twig',
                [
                    'title' => 'Create your username',
                    'form' => $form->createView()
                ]
            );
        } else {
            // User already accepted agreement
            return $this->redirectToRoute('b_home');
        }
    }

    /**
     * @Route("/agreement", name="b_agreement")
     */
    public function userAgreement()
    {
        return $this->render(
            'backend/admin-user-agreement.html.twig', ['title' => 'User agreement and code of conduct']
        );
    }

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
     * @Route("/ip_location/json/{type}", name="b_iptolocation")
     */
    public function apiExternalIpToLocation(Request $r, $type)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }

        $client = HttpClient::create();
        $ip = $r->getClientIp();
        if ($ip == '127.0.0.1') {
            $response = $client->request('GET', $_ENV['API_IP_ECHO'], $options);
            if ($response->getStatusCode() === 200) {
                $ip = $response->getContent();
            } else {
                $this->createNotFoundException('IP could not be determined using API_IP_ECHO');
            }
        }

        $locationApi = str_replace("{{IP}}", $ip, $_ENV['API_IP_LOCATION']);

        $response = $client->request('GET', $locationApi, $options);
        $r = new JsonResponse();

        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $json = json_decode($content);
            switch ($type) {
                case 'timezone':
                    $geo = [
                        'continent' =>$json->continent_name,
                        'region'    =>$json->region_name,
                        'timezone'  =>$json->continent_name."/".$json->region_name
                    ];
                    $content = json_encode($geo);
                    break;
                case 'location':
                    $content = json_encode($json->location);
                    break;
                case 'language':
                    $content = json_encode($json->location->languages[0]);
                    break;
                case 'geo':
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
