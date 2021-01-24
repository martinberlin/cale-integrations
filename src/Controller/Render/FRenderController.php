<?php
namespace App\Controller\Render;

use App\Command\DownloadCryptoCommand;
use App\Entity\IntegrationApi;
use App\Entity\TemplatePartial;
use App\Entity\UserApiFinancialChart;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiFinancialChartRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to render Financial/ Crypto related APIs
 * @Route("/financial")
 */
class FRenderController extends AbstractController
{

    /**
     * render_candlesticks_html is internally called
     * @Route("/candlesticks-html", name="render_candlesticks_html")
     */
    public function render_candlesticks_img(TemplatePartial $partial)
    {
        $api = $partial->getIntegrationApi();
        $html = <<<EOT
        <img src="">
EOT;

        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }

    /**
     * render CSV data using PHP plot
     * PHPlot Example: OHLC (Financial) plot, Filled Candlesticks plot, using
     * external data file, data-data format with date-formatted labels.
     * @Route("/candlestick-png/{userId}/{intApiId}", name="render_crypto_candlesticks")
     * @param $userId
     * @param $intApiId
     * @param UserApiFinancialChartRepository $userFinancialRepository
     * @param UserRepository $userRepository
     */
    public function phpPlotCandlesticks($userId, $intApiId, UserApiFinancialChartRepository $userFinancialRepository, UserRepository $userRepository)
    {
        $financial = $userFinancialRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$financial instanceof UserApiFinancialChart) {
            // Drop plot error and exit
            $plot = new \PHPlot(400, 300);
            $plot->SetUseTTF(true);
            $plot->DrawMessage("Financial settings not found");
            exit();
        }
        // Retrieve settings directly instantiating command: Not needed for now, only if we should retrieve download array: $cryptoSettings->downloadLocalPath
        // $cryptoSettings = new DownloadCryptoCommand($this->container);
        // UPDATE With dynamic Symbol / Timeseries
        $datafile = $_ENV["CRYPTO_BASEURL"].
            "Bitstamp_".$financial->getSymbol().'_'.$financial->getTimeseries().'.csv';

        $plot = new \PHPlot($financial->getWidth(), $financial->getHeight());
        $plot->SetUseTTF(true);

        if (!file_exists($datafile)) {
            // Drop plot error and exit
            $plot->DrawMessage("Data file: ".basename($datafile)." not found");
            exit();
        }

        $plot->SetFont('x_label', null, 8);
        $plot->SetFont('y_label', null, 9);
        $parseCsv = $this->read_prices_data_data($datafile, $financial->getDataRows(), $financial->getTimeseries());

        $plot->SetImageBorderType('plain'); // Improves presentation in the manual
        $plot->SetTitle(strtoupper(substr(basename($datafile),0,-4)).' rows processed: '.$parseCsv[1]);
        $plot->SetDataType('data-data');
        $plot->SetDataValues($parseCsv[0]);

        // candlesticks  or candlesticks2 (filled)
        $plot->SetPlotType($financial->getCandleType());
        $plot->SetDataColors([
            $financial->getColorDescending(), $financial->getColorAscending(), $financial->getColorDescending(), $financial->getColorAscending()
        ]);
        $plot->SetXLabelAngle(90);
        $appendHour = ($financial->getTimeseries() === '1h') ? ' %H' : '';
        // $user->getDateFormat() -> Not the right format  m.d.Y needs %d
        $plot->SetXLabelType('time', "%d.%m.%y".$appendHour);
        $plot->TuneYAutoRange('n', 'T');

        //dump($financial,$cryptoSettings->downloadLocalPath,$cryptoSettings->datafiles);exit();

        // Don't need a Symfony response, plot does this already
        //$response = new Response();$response->headers->set('Content-Type', 'image/png');
        $plot->DrawGraph();
    }

     /**
      Read historical price data from a CSV data downloaded from Yahoo! Finance.
      The first line is a header which must contain: Date,Open,High,Low,Close[...]
      Each additional line has a date (YYYY-MM-DD), then 4 price values.
      Convert to PHPlot data-data data array w7ith empty labels and time_t X
      values and return the data array
    */
    private function read_prices_data_data($filename, $rowsToRender, $renderType)
    {
        //$now = time();
        $now = strtotime(date('Y-m-d 00:01:00')); // Last hour retrieved is always 00:00:00
        $f = fopen($filename, 'r');
        if (!$f) {
            fwrite(STDERR, "Failed to open: $filename\n");
            return FALSE;
        }
        // Read the file
        $row = fgetcsv($f);
        // Remove 1st line: https://www.CryptoDataDownload.com
        $row = fgetcsv($f);
        $count = 0;
        // Read the rest of the file and convert.
        while ($d = fgetcsv($f)) {
            if ($d[3]<0.1 && $d[4]<0.1 && $d[5]<0.1 & $d[6]<0.1) break;

            switch ($renderType) {
                case '1h':
                    if (($now-$d[0]) / 3600 > $rowsToRender) break 2;
                    break;
                case 'd':
                    // Correction 86400 is one day in seconds (Otherwise gives 2 days less)
                    if (($now-$d[0]-(86400*2)) / 86400 > $rowsToRender) break 2;
                    break;
            }
            // $d[0] unixtimestamp use strtotime(date) if comes as string
            $data[] = array('',$d[0], $d[3], $d[4], $d[5], $d[6]);
            $count++;
        }
        fclose($f);

        return [$data, $count];
    }
}