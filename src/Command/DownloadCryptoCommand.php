<?php
// download-crypto-data refactored as Symfony Command
namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCryptoCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'download:crypto';
    private $container;
    private $basePath = 'http://www.cryptodatadownload.com/cdd/';
    private $downloadLocalPath;
    private $datafiles = [];
    private $tools;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
        $this->downloadLocalPath = $_ENV["CRYPTO_BASEURL"];
        // Simply downloads a CSV and stores it in a certain location where it can be read later
        $this->tools = new Tools($this->downloadLocalPath);

        // Daily: d
        $this->datafiles['d'][0] = 'Bitstamp_BTCUSD_d.csv';
        $this->datafiles['d'][1] = 'Bitstamp_ETHUSD_d.csv';
        $this->datafiles['d'][2] = 'Bitstamp_LTCUSD_d.csv';

        // Hourly: h
        $this->datafiles['h'][0] = 'Bitstamp_BTCUSD_1h.csv';
        $this->datafiles['h'][1] = 'Bitstamp_ETHUSD_1h.csv';
        $this->datafiles['h'][2] = 'Bitstamp_LTCUSD_1h.csv';
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Downloads CSV data from cryptodatadownload.com')
            ->addOption(
                't', null,
                InputOption::VALUE_REQUIRED,
                '--t type of download: d (daily) h (hourly) a (all)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('t');
        if (!isset($type) || $type === '') {
            $output->writeln('<error>-t type is REQUIRED</error>');
            return 0;
        }

        switch ($type) {
            case 'd':
                echo "Daily option selected. Downloading:\n";
                foreach ($this->datafiles['d'] as $file) {
                    $downloadUrlPath = $this->basePath.$file;
                    echo "\n$downloadUrlPath";
                    $downloadOK = ($this->tools->download($downloadUrlPath)) ? 'OK' : 'error';
                    if ($downloadOK === 'OK') {
                        // Remove https://www.CryptoDataDownload.com
                        $this->tools->removeFirstLine($this->downloadLocalPath.$file);
                    }

                    $output->writeln("<success>Seconds taken: ".round($this->tools->getTimer(),2)." $downloadOK</success>");
                }
                break;

            case 'h':
                echo "Hourly option selected. Downloading\n";
                foreach ($this->datafiles['h'] as $file) {
                    $downloadUrlPath = $this->basePath.$file;
                    echo "\n$downloadUrlPath";
                    $downloadOK = ($this->tools->download($downloadUrlPath)) ? 'OK' : 'error';
                    if ($downloadOK === 'OK') {
                        // Remove https://www.CryptoDataDownload.com
                        $this->tools->removeFirstLine($this->downloadLocalPath.$file);
                    }

                    $output->writeln("<success>Seconds taken: ".round($this->tools->getTimer(),2)." $downloadOK</success>");
                }
                break;
        }


        $output->writeln("<info>DONE</info>");
        return 1;
    }
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