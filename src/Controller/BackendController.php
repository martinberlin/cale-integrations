<?php
namespace App\Controller;

use App\Entity\Display;
use App\Form\UsernameAgreementType;
use App\Repository\SysScreenLogRepository;
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
use Symfony\Contracts\Translation\TranslatorInterface;

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
                'title' => 'Admin dashboard',
                'hasScreen' => count($this->getUser()->getScreens())
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

    private function datatablesScreenColumns(&$json, $isAdmin = false) {
        $json['columns'][] = (object)['data' => 'screen',   'n'=>'Screen'];
        if ($isAdmin) {
            $json['columns'][] = (object)['data' => 'user',  'n'=>'User'];
        }
        $json['columns'][] = (object)['data' => 'created',  'n'=>'Access'];
        $json['columns'][] = (object)['data' => 'pixels',   'n'=>'Pixels'];
        $json['columns'][] = (object)['data' => 'b',        'n'=>'Bytes'];
        $json['columns'][] = (object)['data' => 'millis',   'n'=>'Millis'];
        $json['columns'][] = (object)['data' => 'ip',       'n'=>'IP'];
        if (!$isAdmin) {
            $json['columns'][] = (object)['data' => 'cached', 'n' => 'Cache hit'];
        }
    }

    /**
     * @Route("/json/data/{type}", name="b_json_datatables")
     */
    public function datatablesJson(Request $request, $type,
                                   SysScreenLogRepository $screenLogRepository, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $response = new JsonResponse();
        $json = [];
        $json['data'] = [];
        $datatablesDateFormat = $this->getParameter('datatables')['date_format'];
        $amount = $request->get('amount', 100);

        switch ($type) {
            case 'screen_log_purge':
                $logs = $screenLogRepository->findBy(['user' => $this->getUser()],['created' => 'ASC'],$amount);
                $count = 0;
                foreach ($logs as $log){
                    try {
                        $em->remove($log);
                        $count++;
                    } catch(\Exception $e ) {
                        $count--;
                    }
                }
                $json = ['status' => $count.' '.$translator->trans('logs_purged')];
                $em->flush();
                break;

            case 'screen_log':
                $logs = $screenLogRepository->findBy(['user' => $this->getUser()],['created' => 'DESC'],1000);

                foreach ($logs as $log){
                    $display = $log->getScreen()->getDisplay();
                    $created = $log->getCreated()+date("Z");
                    $json['data'][] = [
                        'created'=> gmdate($datatablesDateFormat, $created),
                        'screen' => $log->getScreen()->getId(),
                        'pixels' => ($display instanceof Display) ? $display->getWidth().'x'.$display->getHeight() : '',
                        'b'      => $log->getBytes(),
                        'millis' => $log->getMillis(),
                        'ip'     => $log->getInternalIp(),
                        'cached' => ($log->isCached()) ? 'Yes' : 'No'
                    ];
                }
                $this->datatablesScreenColumns($json);
                break;

            case 'screen_log_admin':
                $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'No report without ROLE_ADMIN');
                $logs = $screenLogRepository->findBy([],[],2000);

                foreach ($logs as $log){
                    $display = $log->getScreen()->getDisplay();
                    $created = $log->getCreated()+date("Z");
                    $json['data'][] = [
                        'created'=> gmdate($datatablesDateFormat, $created),
                        'screen' => $log->getScreen()->getId(),
                        'user'  => $log->getScreen()->getUser()->getName(),
                        'pixels' => ($display instanceof Display) ? $display->getWidth().'x'.$display->getHeight() : '',
                        'b'      => $log->getBytes(),
                        'millis' => $log->getMillis(),
                        'ip'     => $log->getInternalIp(),
                        'cached' => ($log->isCached()) ? 'Yes' : 'No'
                    ];
                }
                $this->datatablesScreenColumns($json, true);
                break;

            default:
                $this->createNotFoundException('No report of this type');
                break;
        }
        $encodedJson = json_encode($json);
        $response->setContent($encodedJson);
        return $response;
    }

    }
