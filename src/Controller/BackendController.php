<?php
namespace App\Controller;

use App\Entity\Display;
use App\Entity\ShippingTracking;
use App\Form\UsernameAgreementType;
use App\Repository\ShippingTrackingRepository;
use App\Repository\SysScreenLogRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/backend")
 */
class BackendController extends AbstractController
{
    public function isMobile(Request $request)
    {
        $useragent = $request->headers->get('User-Agent');
        if (!$useragent) {
            return false;
        }
        //return true; // uncomment to Force mobile preview
        return (
            preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) ||
            preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent,0,4))
        );
    }

    /**
     * @Route("/", name="b_home")
     */
    public function home(Request $request, ShippingTrackingRepository $shipRepository, EntityManagerInterface $entityManager)
    {
        if ($request->get('received')) {
            $shipment = $shipRepository->findOneBy(['tracking'=>$request->get('received')]);
            if ($shipment instanceof ShippingTracking ===false) {
                $this->addFlash('error', 'Sending with tracking '.$request->get('received').' not found');
            } else {
                $shipment->setStatus('received');
                $shipment->setArchived(true);
                $entityManager->persist($shipment);
                $entityManager->flush();
                $this->addFlash('success', 'Package marked as received. Thanks!');
            }
        }
        $shippings = $shipRepository->getForUser($this->getUser());

        return $this->render(
            'backend/admin-home.html.twig',
            [
                'title' => 'User dashboard',
                'version' => $this->getParameter('version'),
                'hasScreen' => count($this->getUser()->getScreens()),
                'isMobile'  => $this->isMobile($request),
                'shippings' => $shippings,
                'menu' => 'user'
            ]
        );
    }

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
                        $this->addFlash('success', "Please confirm you want to terminate your account");
                        return $this->redirectToRoute('b_user_terminate');
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
                                return $this->redirectToRoute('b_user_profile', ['firstTime' => 1]);
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
                    'form' => $form->createView(),
                    'show_menu' => false
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
            'backend/admin-user-agreement.html.twig', ['title' => 'User agreement and code of conduct','menu' => 'user']
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

    private function datatablesColumnsMobile(&$json) {
        $json['columns'][] = (object)['data' => 'screen',   'n'=>'Scr'];
        $json['columns'][] = (object)['data' => 'created',  'n'=>'Access'];
        $json['columns'][] = (object)['data' => 'w',        'n'=>'Width'];
        $json['columns'][] = (object)['data' => 'b',        'n'=>'Bytes'];
        $json['columns'][] = (object)['data' => 'ip',       'n'=>'IP'];
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

            case 'screen_log_mobile':
                $logs = $screenLogRepository->findBy(['user' => $this->getUser()],['created' => 'DESC'],1000);

                foreach ($logs as $log){
                    $display = $log->getScreen()->getDisplay();
                    $created = $log->getCreated()+date("Z");
                    $json['data'][] = [
                        'screen' => substr($log->getScreen()->getId(),-3),
                        'created'=> gmdate($datatablesDateFormat, $created),
                        'w' => ($display instanceof Display) ? $display->getWidth() : '',
                        'b' => $log->getBytes(),
                        'ip'=> $log->getInternalIp()
                    ];
                }
                $this->datatablesColumnsMobile($json);
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
                $logs = $screenLogRepository->findBy([],['created' => 'DESC'],2000);

                foreach ($logs as $log){
                    $display = $log->getScreen()->getDisplay();
                    $created = $log->getCreated()+date("Z");
                    $json['data'][] = [
                        'created'=> gmdate('y.m.d H:i', $created),
                        'screen' => substr($log->getScreen()->getId(),-3),
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
