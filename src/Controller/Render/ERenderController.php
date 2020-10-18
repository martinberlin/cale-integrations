<?php
namespace App\Controller\Render;

use App\Entity\IntegrationApi;
use App\Entity\TemplatePartial;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to render Crypto related APIs
 * @Route("/crypto")
 */
class ERenderController extends AbstractController
{

    /**
     * render_etherscan is internally called
     * @Route("/etherscan", name="render_etherscan")
     */
    public function render_etherscan(TemplatePartial $partial)
    {
        $api = $partial->getIntegrationApi();
        $icon = ($api->getImagePath()!=='') ? '<img src="/assets/svg/api/etherscan/'.$api->getImagePath().'">' : '♦';
        $ethHtml = $this->etherscanQuery($api);
        $html = <<<EOT
        <table>
                <tbody>
                <tr>
                    <td style="width:20%" class="text-right">
                        $icon
                    </td>
                    <td style="width:80%;padding-left:0.5em">
                        $ethHtml
                    </td>
                </tr>
                </tbody>
            </table>
EOT;

        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }

    /**
     * Return directly the HTML for Balance or TX
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function etherscanQuery(IntegrationApi $api)
    {
        $options = [];
        if (isset($_ENV['API_PROXY'])) {
            $options = array('proxy' => 'http://'.$_ENV['API_PROXY']);
        }
        $userApi = $api->getUserApi();
        $apiConfig = $userApi->getApi();
        $apiKey = $userApi->getAccessToken();
        $jsonConfig = json_decode($api->getJsonSettings());
        // Prepare URL
        // https://api.etherscan.io/api?module=[module]&action=[action]&address=[address]&apikey=[apikey]
        $url = str_replace('[module]','account', $apiConfig->getUrl());
        $url = str_replace('[action]',$jsonConfig->action, $url);
        $url = str_replace('[address]',$jsonConfig->address, $url);
        $url = str_replace('[apikey]',$apiKey, $url);
        $client = HttpClient::create();
        $response = $client->request('GET', $url."&sort=desc", $options);

        $html = '';
        $priceHtml = '';
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent());
            switch ($jsonConfig->action) {
                case 'balance':
                    $ethValue = $data->result / pow(10, 18);
                    $eth = round($ethValue, 4);
                    if ($jsonConfig->showConversionPrice) {
                        $priceQuery = $client->request('GET','https://api.etherscan.io/api?module=stats&action=ethprice');
                        if ($priceQuery->getStatusCode() === 200) {
                            try {
                                $parsed = true;
                                $price = json_decode($priceQuery->getContent());
                            } catch (\Exception $exception) {
                                $parsed = false;
                            }
                            if ($parsed && property_exists($price->result,'ethusd')) {
                                $priceHtml = round($price->result->ethusd * $ethValue, 2);
                                $priceHtml .= " USD";
                            }
                        }
                    }
                    $html .= '<b style="font-size:20px"><span style="font-size:25px">'.$eth.' ETH</span></b><br>';
                    $html .= $priceHtml;
                    break;

                case 'txlist':
                    if ($jsonConfig->showConversionPrice) {
                        //sleep(1);
                        $priceQuery = $client->request('GET','https://api.etherscan.io/api?module=stats&action=ethprice');
                        if ($priceQuery->getStatusCode() === 200) {
                            try {
                                $parsed = true;
                                $price = json_decode($priceQuery->getContent());
                            } catch (\Exception $exception) {
                                $parsed = false;
                            }
                            if ($parsed && property_exists($price->result,'ethusd')) {
                                $ethusd = $price->result->ethusd;
                                $ethbtc = $price->result->ethbtc;
                                $html.=  "<b style=\"font-size:20px\">1 ETH $ethusd USD $ethbtc BTC</b><br>";
                            }
                        }
                        if ($data->message === 'OK')  {
                            $html.= "<table style=\"font-size:18px\"><thead><tr><th>From</th><th>To</th><th>Value</th></th></thead><tbody>";
                            $count = 0;
                            foreach ($data->result as $row) {
                                ++$count;
                                if ($count>$jsonConfig->numberOfTransactions) break;
                                $from = substr($row->from,0,12).'...';
                                $to = substr($row->to,0,12).'...';
                                $value = round($row->value / pow(10, 18),2);
                                $html.="<tr><td>$from</td><td>$to</td><td>$value</td></tr>";
                            }
                            $html.="</tbody></table>";
                        }
                    }
                    break;
            }

        } else {
            $html = "Etherscan returned status:".$response->getStatusCode();
        }
        return $html;
    }
}