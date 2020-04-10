<?php
namespace App\Controller\Api;

use App\Entity\Display;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    private function datatablesColumns(&$json) {
        $json['columns'][] = (object)['data' => 'screen', 'n'=>'screen'];
        $json['columns'][] = (object)['data' => 'size',  'n'=>'size'];
        $json['columns'][] = (object)['data' => 'id',    'n'=>'id'];
    }

    private function imageUrlGenerator($isSsl, $responseType, $username, $screenId) {
        $schema = ($isSsl) ? 'https://' : 'http://';
        $url = $schema.$_ENV['SCREENSHOT_TOOL'].'/'.$responseType.'/'.$username.'/'.$screenId;
        return $url;
    }

    /**
     * @Route("/{key}/screens", name="api_screen")
     */
    public function screens($key, UserRepository $userRepository)
    {
        $r = new JsonResponse();
        $user = $userRepository->findOneBy(['apiKey' => $key]);
        if ($user instanceof User===false) {
            $r->setContent('{"error":"Wrong API key"}');
            return $r;
        }
        $userScreens = $user->getScreens();
        $json = [];
        if (!$userScreens->count()) {
            $json['data'][] = [
                'name' => 'No screens defined in ',
                'size' => 'cale.es',
                'id' => ''
            ];
        } else {
            $wifis = $user->getUserWiFis();
            $baseconfig = [];
            for($i = 0; $i<3; $i++) {
                $index = $i+1;
                if (isset($wifis[$i])) {
                    $baseconfig['wifi_ssid'.$index] = $wifis[$i]->getWifiSsid();
                    $baseconfig['wifi_pass'.$index] = $wifis[$i]->getWifiPass();
                }
            }
            // screen_url  bearer   wifi_ssid1 wifi_pass1
            foreach ($userScreens as $s) {
                $display = $s->getDisplay();
                $isDisplayAssigned = $display instanceof Display;
                $imageType = ($isDisplayAssigned && $display->getType()==='eink') ?'bmp':'jpg';
                $config = $baseconfig;
                $config['screen_url'] = ($isDisplayAssigned) ?
                    $this->imageUrlGenerator($s->isOutSsl(), $imageType, $user->getName(), $s->getId()): '';
                $config['bearer'] = $s->getOutBearer();
                $json['data'][] = [
                    'screen' => $s->getName(),
                    'size' => is_null($display) ? '' : $display->getWidth().'*'.$display->getHeight(),
                    'id' => $s->getId()
                ];
                $json[$s->getId()] = json_encode($config);
            }
        }
        $this->datatablesColumns($json);
        $r->setContent(json_encode($json));
        return $r;
    }
}