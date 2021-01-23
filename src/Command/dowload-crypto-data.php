<?php
// Not part of Symfony files, initial plain PHP that I made fast to download CSV from cryptodatadownload
// Simply downloads a CSV and stores it in a certain location where it can be read later
$basePath = 'http://www.cryptodatadownload.com/cdd/';
$downloadLocalPath = '/var/www/html/plot/data/';
$tools = new Tools($downloadLocalPath);

$datafiles = [];
// Daily: d
$datafiles['d'][0] = 'Bitstamp_BTCUSD_d.csv';
$datafiles['d'][1] = 'Bitstamp_ETHUSD_d.csv';
$datafiles['d'][2] = 'Bitstamp_LTCUSD_d.csv';

// Hourly: h
$datafiles['h'][0] = 'Bitstamp_BTCUSD_1h.csv';
$datafiles['h'][1] = 'Bitstamp_ETHUSD_1h.csv';
$datafiles['h'][2] = 'Bitstamp_LTCUSD_1h.csv';

$options = getopt('t:', ['type:']);
if (!count($options) || !array_key_exists('t', $options)) {
    exit("-t type (d: daily h: hourly) is mandatory\n");
}

switch ($options['t']) {
    case 'd':
      echo "Daily option selected. Downloading:\n";
        foreach ($datafiles['d'] as $file) {
            $downloadUrlPath = $basePath.$file;
            echo "\n$downloadUrlPath";
            $downloadOK = ($tools->download($downloadUrlPath)) ? 'OK' : 'error';
            if ($downloadOK === 'OK') {
                // Remove https://www.CryptoDataDownload.com
                $tools->removeFirstLine($downloadLocalPath.$file);
            }

            echo("\nSeconds taken: ".round($tools->getTimer(),2)." $downloadOK\n\n");
        }
        break;

    case 'h':
        echo "Hourly option selected. Downloading\n";
        foreach ($datafiles['h'] as $file) {
            $downloadPath = $basePath.$file;
            echo "\n$downloadPath";
            $downloadOK = ($tools->download($downloadPath)) ? 'OK' : 'error';
            if ($downloadOK === 'OK') {
                $tools->removeFirstLine($downloadLocalPath.$file);
            }

            echo("\nSeconds taken: ".round($tools->getTimer(),2)."\n\n");
        }
        break;
}


class Tools
{
    private $startTime;
    private $callsCounter;
    private $downloadPath;

    function __construct(string $downloadPath)
    {
        $this->startTime = microtime(true);
        $this->callsCounter = 0;
        $this->downloadPath = $downloadPath;
    }

    public function getTimer(): float
    {
        $timeEnd = microtime(true);
        $time = $timeEnd - $this->startTime;
        $this->callsCounter++;
        return $time;
    }
    public function download(string $url)
    {
        $f = fopen($url, 'r');
        if (!$f) {
            fwrite(STDERR, "Failed to open: $url\n");
            return FALSE;
        }

        return file_put_contents($this->downloadPath.basename($url), $f);
    }

    public function removeFirstLine(string $logFile) {
        $firstline = false;
        if($handle = fopen($logFile,'c+')){
            if(!flock($handle,LOCK_EX)){fclose($handle);}
            $offset = 0;
            $len = filesize($logFile);
            while(($line = fgets($handle,4096)) !== false){
                if(!$firstline){$firstline = $line;$offset = strlen($firstline);continue;}
                $pos = ftell($handle);
                fseek($handle,$pos-strlen($line)-$offset);
                fputs($handle,$line);
                fseek($handle,$pos);
            }
            fflush($handle);
            ftruncate($handle,($len-$offset));
            flock($handle,LOCK_UN);
            fclose($handle);
        }
    }

    public function getCallsNumber(): int
    {
        return $this->callsCounter;
    }
}