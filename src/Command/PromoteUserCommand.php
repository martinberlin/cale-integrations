<?php
namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PromoteUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'user:promote';
    private $container;
    private $userRepository;

    public function __construct(ContainerInterface $container, UserRepository $userRepository)
    {
        parent::__construct();
        $this->container = $container;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Promotes a user to ROLE_ADMIN')
            ->addOption(
                'e', null,
                InputOption::VALUE_REQUIRED,
                '--e email from the user'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getOption('e');
        if (!isset($email) || $email === '') {
            $output->writeln('<error>-e email is REQUIRED</error>');
            return 0;
        }
        $em = $this->container->get('doctrine')->getManager();

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user instanceof User === false) {
            $output->writeln("<error>User identified by $email not found</error>");
            return 0;
        }
        $user->setRoles(['ROLE_ADMIN']);
        $em->persist($user);
        $em->flush();
        $output->writeln("<info>User $email promoted to ROLE_ADMIN</info>");
        return 1;
    }
}