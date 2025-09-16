<?php
namespace App\Controller\Render;

use App\Command\DownloadCryptoCommand;
use App\Entity\IntegrationApi;
use App\Entity\TemplatePartial;
use App\Entity\UserApiAmpereSettings;
use App\Entity\UserApiFinancialChart;
use App\Entity\UserApiLogChart;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiAmpereSettingsRepository;
use App\Repository\UserApiFinancialChartRepository;
use App\Repository\UserApiLogChartRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * This controller is responsible to render Financial/ Crypto related APIs
 * @Route("/financial")
 */
class FRenderController extends AbstractController
{

    /**
     * render_int_crypto is internally called
     * @Route("/render_int_crypto", name="render_int_crypto")
     */
    public function render_int_crypto(TemplatePartial $partial)
    {
        $api = $partial->getIntegrationApi();
        $intApiId = $api->getId();
        $userId = $api->getUserApi()->getUser()->getId();
        $imgSrc = $this->generateUrl('render_crypto_candlesticks', [
            'userId'   => $userId,
            'intApiId' => $intApiId
        ], UrlGenerator::ABSOLUTE_URL);
        $html = <<<EOT
        <img src="$imgSrc">
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
     * @Route("/candlestick-img/{userId}/{intApiId}", name="render_crypto_candlesticks")
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
            $plot->DrawMessage("Financial settings not found");
            exit();
        }
        // Retrieve settings directly instantiating command: Not needed for now, only if we should retrieve download array: $cryptoSettings->downloadLocalPath
        // $cryptoSettings = new DownloadCryptoCommand($this->container);
        // UPDATE With dynamic Symbol / Timeseries
        $datafile = $_ENV["CRYPTO_BASEURL"].
            "Bitstamp_".$financial->getSymbol().'_'.$financial->getTimeseries().'.csv';

        $plot = new \PHPlot($financial->getWidth(), $financial->getHeight());
        //$plot->SetUseTTF(true);


        if (!file_exists($datafile)) {
            // Drop plot error and exit
            $plot->DrawMessage("Data file: ".basename($datafile)." not found");
            exit();
        }

        // Set label fonts:
        $elements = ['x_label','y_label'];
        foreach ($elements as $element) {
            $plot->SetFontTTF($element, $_ENV["FONTS_BASEURL"].$financial->getAxisFontFile(), $financial->getAxisFontSize());
        }

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
        $plot->SetFileFormat('jpg');
        //dump($financial,$cryptoSettings->downloadLocalPath,$cryptoSettings->datafiles);exit();
        $image = $plot->DrawGraph();

        // Don't need a Symfony response, plot does this already
        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent($image);
        return $response;
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

    /**
     * render SCD40 data is internally called
     * @Route("/render_scd40", name="render_int_scd40")
     */
    public function render_int_scd40(TemplatePartial $partial, UserApiLogChartRepository $logChartRepository)
    {
        $api = $partial->getIntegrationApi();
        $intApiId = $api->getId();
        $userId = $api->getUserApi()->getUser()->getId();
        $imgSrc = $this->generateUrl('render_scd40_chart', [
            'userId'   => $userId,
            'intApiId' => $intApiId
        ], UrlGenerator::ABSOLUTE_URL);
        $html = <<<EOT
        <img src="$imgSrc">
EOT;
        $logChartSettings = $logChartRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        if ($logChartSettings instanceof UserApiLogChart && $logChartSettings->getAdditionalChartCo2()) {
            $imgSrc = $this->generateUrl('render_scd40_co2_chart', [
                'userId'   => $userId,
                'intApiId' => $intApiId
            ], UrlGenerator::ABSOLUTE_URL);
            $html .= <<<EOT
<br><img src="$imgSrc">
EOT;
        }
        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }

    /**
     * render CSV data using PHP plot
     * @Route("/scd40-chart/{userId}/{intApiId}", name="render_scd40_chart")
     * @param $userId
     * @param $intApiId
     * @param UserApiLogChartRepository $userApiLogChartRepository
     * @param UserRepository $userRepository
     */
    public function phpPlotSCD40($userId, $intApiId, UserApiLogChartRepository $userApiLogChartRepository, UserRepository $userRepository)
    {
        $logChartSettings = $userApiLogChartRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$logChartSettings instanceof UserApiLogChart) {
            // Drop plot error and exit
            $plot = new \PHPlot(400, 300);
            $plot->DrawMessage("SCD40 Chart settings not found");
            exit();
        }
        $response = $this->forward("App\Controller\ApiLogController::logRead", [
            'key' => $logChartSettings->getIntApi()->getId(),
            'length' => $logChartSettings->getDataRows()
        ]);

        try {
            $parsed = json_decode($response->getContent(), $associative=true, $depth=512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        // Remove timezone for this chart
        foreach ($parsed['data'] as $key => $value) {
            unset($parsed['data'][$key]['timezone']);
            if ($logChartSettings->getExclude2()) {
                unset($parsed['data'][$key]['humidity']);
            }
            if ($logChartSettings->getExclude1()) {
                unset($parsed['data'][$key]['co2']);
            }
        }
        $data = $parsed['data'];

        $plot = new \PHPlot($logChartSettings->getWidth(), $logChartSettings->getHeight());
        $plot->setTitle($logChartSettings->getIntApi()->getName());
        # Make a legend for the 3 data sets plotted:
        $legends = ['Temp'];
        $colors = [$logChartSettings->getColor1()];
        if (! $logChartSettings->getExclude2()) {
            array_push($legends, 'Hum');
            array_push($colors, $logChartSettings->getColor2());
        }
        if (! $logChartSettings->getExclude1()) {
            array_push($legends, 'CO2');
            array_push($colors, $logChartSettings->getColor3());
        }
        // Set label fonts:
        $elements = ['x_label','y_label'];
        foreach ($elements as $element) {
            $plot->SetFontTTF($element,
                $_ENV["FONTS_BASEURL"].$logChartSettings->getAxisFontFile(),
                $logChartSettings->getAxisFontSize());
        }

        $plot->SetLegend($legends);
        $plot->SetLegendPixels(80,20);
        $plot->SetDataColors($colors);
        // If we add new boolean to show Y values
        if (false) {
            # Turn on Y data labels:
            $plot->SetYDataLabelPos('plotin');
        }
        if (! $logChartSettings->getShowXTickChart1()) {
            $plot->setXDataLabelPos('none');
            $plot->SetXTickLabelPos('none');
        }
        $plot->SetMarginsPixels(55, 50);
        $plot->SetXDataLabelAngle(45);
        $plot->SetLineWidths(3);
        $plot->SetImageBorderType('plain');
        $plot->SetPlotType($logChartSettings->getCandleType());
        $plot->SetDataType('text-data'); // data-data-xyz
        $plot->SetDataValues($data);
        $plot->SetFileFormat('jpg');
        $image = $plot->DrawGraph();

        // Don't need a Symfony response, plot does this already
        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent($image);
        return $response;
    }

    /**
     * render CO2 CSV data using PHP plot
     * @Route("/scd40-co2-chart/{userId}/{intApiId}", name="render_scd40_co2_chart")
     * @param $userId
     * @param $intApiId
     * @param UserApiLogChartRepository $userApiLogChartRepository
     * @param UserRepository $userRepository
     */
    public function phpPlotSCD40co2($userId, $intApiId, UserApiLogChartRepository $userApiLogChartRepository, UserRepository $userRepository)
    {
        $logChartSettings = $userApiLogChartRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$logChartSettings instanceof UserApiLogChart) {
            // Drop plot error and exit
            $plot = new \PHPlot(400, 300);
            $plot->DrawMessage("SCD40 Chart settings not found");
            exit();
        }
        $response = $this->forward("App\Controller\ApiLogController::logRead", [
            'key' => $logChartSettings->getIntApi()->getId(),
            'length' => $logChartSettings->getDataRows()
        ]);

        try {
            $parsed = json_decode($response->getContent(), $associative=true, $depth=512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        // Remove timezone for this chart
        foreach ($parsed['data'] as $key => $value) {
            unset($parsed['data'][$key]['timezone']);
            unset($parsed['data'][$key]['humidity']);
            unset($parsed['data'][$key]['temperature']);
        }
        $data = $parsed['data'];
        //dump($data);exit();
        $plot = new \PHPlot($logChartSettings->getWidth(), $logChartSettings->getHeight());
        $plot->setTitle("CO2 concentration (ppm)");
        # Make a legend for the 3 data sets plotted:
        $legends = ['Co2'];
        $colors = [$logChartSettings->getColor3()];
        // Set label fonts:
        $elements = ['x_label','y_label'];
        foreach ($elements as $element) {
            $plot->SetFontTTF($element,
                $_ENV["FONTS_BASEURL"].$logChartSettings->getAxisFontFile(),
                $logChartSettings->getAxisFontSize());
        }

        $plot->SetLegend($legends);
        $plot->SetLegendPixels(80,20);
        $plot->SetDataColors($colors);
        if (false) {
            $plot->SetYDataLabelPos('plotin');
        }
        if (! $logChartSettings->getShowXTickChart2()) {
            $plot->setXDataLabelPos('none');
            $plot->SetXTickLabelPos('none');
        }
        $plot->SetMarginsPixels(55, 50);

        $plot->SetXDataLabelAngle(45);
        $plot->SetLineWidths(3);
        $plot->SetImageBorderType('plain');
        $plot->SetPlotType($logChartSettings->getCo2ChartType());
        $plot->SetDataType('text-data'); // data-data-xyz
        $plot->SetDataValues($data);
        $plot->SetFileFormat('jpg');
        $image = $plot->DrawGraph();

        // Don't need a Symfony response, plot does this already
        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent($image);
        return $response;
    }

    // AMPERE
    /**
     * render Electricity Consumption using PZEM IOT
     * @Route("/render_int_ampere", name="render_int_ampere")
     */
    public function render_int_ampere(TemplatePartial $partial, UserApiAmpereSettingsRepository $logChartRepository)
    {
        $api = $partial->getIntegrationApi();
        $intApiId = $api->getId();
        $userId = $api->getUserApi()->getUser()->getId();
        $imgSrc = $this->generateUrl('render_ampere_daily_chart', [
            'userId'   => $userId,
            'intApiId' => $intApiId
        ], UrlGenerator::ABSOLUTE_URL);
        $html = <<<EOT
        <img src="$imgSrc">
EOT;
        $logChartSettings = $logChartRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        if ($logChartSettings instanceof UserApiAmpereSettings && $logChartSettings->getAdditionalLiveChart()) {
            $imgSrc = $this->generateUrl('render_ampere_live_chart', [
                'userId'   => $userId,
                'intApiId' => $intApiId
            ], UrlGenerator::ABSOLUTE_URL);
            $html .= <<<EOT
<br><img src="$imgSrc">
EOT;
        }
        // Render the content partial and return the composed HTML
        $response = new Response();
        $response->setContent($html);
        return $response;
    }

    /**
     * render CSV data using PHP plot
     * @Route("/amp-daily-chart/{userId}/{intApiId}", name="render_ampere_daily_chart")
     * @param $userId
     * @param $intApiId
     * @param UserApiAmpereSettingsRepository $userApiLogChartRepository
     * @param UserRepository $userRepository
     */
    public function phpPlotAmpDaily($userId, $intApiId, UserApiAmpereSettingsRepository $userApiLogChartRepository, UserRepository $userRepository)
    {
        $logChartSettings = $userApiLogChartRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        /*dump(['user' => $userId,
            'intApi' => $intApiId]);exit();*/
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$logChartSettings instanceof UserApiAmpereSettings) {
            // Drop plot error and exit
            $plot = new \PHPlot(400, 300);
            $plot->DrawMessage("AMPERE Chart settings not found");
            exit();
        }
        $response = $this->forward("App\Controller\ApiLogController::logEnergyDailyRead", [
            'key' => $logChartSettings->getIntApi()->getId(),
            'length' => $logChartSettings->getDataRows()
        ]);

        try {
            $parsed = json_decode($response->getContent(), $associative=true, $depth=512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        $titleSuffix = "";
        foreach ($parsed['data'] as $key => $value) {
            $titleSuffix = $parsed['data'][$key]['datestamp'];
            unset($parsed['data'][$key]['datestamp']);
        }
        $data = $parsed['data'];
        //dump($data);exit();

        $plot = new \PHPlot($logChartSettings->getWidth(), $logChartSettings->getHeight());
        $plot->setTitle($logChartSettings->getIntApi()->getName().' per hour '."({$titleSuffix})");
        # Make a legend for the 3 data sets plotted:
        $legends = ['kW/h'];
        $colors = [$logChartSettings->getColor1()];

        // Set label fonts:
        $elements = ['x_label','y_label'];
        foreach ($elements as $element) {
            $plot->SetFontTTF($element,
                $_ENV["FONTS_BASEURL"].$logChartSettings->getAxisFontFile(),
                $logChartSettings->getAxisFontSize());
        }

        $plot->SetLegend($legends);
        $plot->SetLegendPixels(80,20);
        $plot->SetDataColors($colors);

        $plot->SetMarginsPixels(55, 50);
        $plot->SetXDataLabelAngle(45);
        $plot->SetLineWidths(3);
        $plot->SetImageBorderType('plain');
        $plot->SetPlotType($logChartSettings->getCandleType());
        $plot->SetDataType('text-data'); // data-data-xyz
        $plot->SetDataValues($data);
        $plot->SetFileFormat('jpg');
        $image = $plot->DrawGraph();

        // Don't need a Symfony response, plot does this already
        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent($image);
        return $response;
    }

    /**
     * render CSV data using PHP plot
     * @Route("/amp-live-chart/{userId}/{intApiId}", name="render_ampere_live_chart")
     * @param $userId
     * @param $intApiId
     * @param UserApiAmpereSettingsRepository $userApiLogChartRepository
     * @param UserRepository $userRepository
     */
    public function phpPlotAmpLive($userId, $intApiId, UserApiAmpereSettingsRepository $userApiLogChartRepository, UserRepository $userRepository)
    {
        $logChartSettings = $userApiLogChartRepository->findOneBy([
            'user' => $userId,
            'intApi' => $intApiId
        ]);
        /*dump(['user' => $userId,
            'intApi' => $intApiId]);exit();*/
        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!$logChartSettings instanceof UserApiAmpereSettings) {
            // Drop plot error and exit
            $plot = new \PHPlot(400, 300);
            $plot->DrawMessage("AMPERE Chart settings not found");
            exit();
        }
        $response = $this->forward("App\Controller\ApiLogController::logEnergyLiveRead", [
            'key' => $logChartSettings->getIntApi()->getId(),
            'length' => $logChartSettings->getDataRows()
        ]);

        try {
            $parsed = json_decode($response->getContent(), $associative=true, $depth=512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Invalid JSON');
        }
        $titleSuffix = "";
        foreach ($parsed['data'] as $key => $value) {
            $titleSuffix = $parsed['data'][$key]['datestamp'];
            $parsed['data'][$key]['fp'] = $parsed['data'][$key]['fp'] * 100;
            unset($parsed['data'][$key]['hr']);
            if ($logChartSettings->getExclude1()) {
                unset($parsed['data'][$key]['v']);
            }
        }
        $data = $parsed['data'];

        //dump($data);exit();
        $plot = new \PHPlot($logChartSettings->getWidth(), $logChartSettings->getHeight());
        $plot->setTitle($logChartSettings->getIntApi()->getName().' per minute '."({$titleSuffix})");
        # Make a legend for the 3 data sets plotted:
        $legends = ['Watt'];
        if (! $logChartSettings->getExclude1()) {
            array_push($legends, 'Voltage');
        }
        array_push($legends, 'Power factor (*100)');
        $colors = [$logChartSettings->getColor1(), $logChartSettings->getColor2()];

        // Set label fonts:
        $elements = ['x_label','y_label'];
        foreach ($elements as $element) {
            $plot->SetFontTTF($element,
                $_ENV["FONTS_BASEURL"].$logChartSettings->getAxisFontFile(),
                $logChartSettings->getAxisFontSize());
        }

        $plot->SetLegend($legends);
        $plot->SetLegendPixels(80,20);
        $plot->SetDataColors($colors);

        $plot->SetMarginsPixels(55, 50);
        $plot->SetXDataLabelAngle(45);
        $plot->SetLineWidths(3);
        $plot->SetImageBorderType('plain');
        $plot->SetPlotType($logChartSettings->getCandleType());
        $plot->SetDataType('text-data'); // data-data-xyz
        $plot->SetDataValues($data);
        $plot->SetFileFormat('jpg');
        $image = $plot->DrawGraph();

        // Don't need a Symfony response, plot does this already
        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent($image);
        return $response;
    }
}