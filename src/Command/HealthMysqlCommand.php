<?php
/**
 * Try to connect to mysql. If it does not work send an email to maintainer
 */
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

class HealthMysqlCommand extends Command
{
    protected static $defaultName = 'health:check';
    private $twig;
    private $mysqlConnection;
    private $mailer;
    private $container;

    public function __construct(Environment $twig, ContainerInterface $container, EntityManagerInterface $manager, \Swift_Mailer $mailer)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->mysqlConnection = $manager->getConnection();
        $this->mailer = $mailer;
        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Try to connect to mysql
        // If it does not succeed then email $_ENV['HEALTH_EMAIL1'] and 2
        $sql = 'SELECT 1';

        $connectionFailed = false;
        try {
            $stmt = $this->mysqlConnection->prepare($sql);
            $stmt->execute();
        } catch (\Exception $exception) {
            $connectionFailed = true;
            $output->writeln("<info>Connection failed</info>");
        }

        if (!$connectionFailed) {
            $output->writeln("Connection OK");
            exit();
        }

        $message = (new \Swift_Message("CRITICAL: Server DB is down"))
            ->setFrom($this->container->getParameter('cale_official_email'))
            ->setTo($_ENV['HEALTH_EMAIL1'])
            ->setCc($_ENV['HEALTH_EMAIL2'])
            ->setBody(
                $this->twig->render(
                    'emails/health_mysql_alert.html.twig',
                    [
                        'body' => 'mysql DB connection failed. CALE and SLOSAREK may be down!'
                    ]
                ),
                'text/html'
            );

        if ($this->mailer->send($message, $failures))
        {
            $output->writeln("Mail is out for HEALTH_EMAIL1 & 2");
        }
    }
}