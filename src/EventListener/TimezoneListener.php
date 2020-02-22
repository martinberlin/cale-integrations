<?php
namespace App\EventListener;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;


final class TimezoneListener
{
    private $security;
    private $logger;

    public function __construct(Security $security, LoggerInterface $logger)
    {
        $this->security = $security;
        $this->logger = $logger;
    }

    public function __invoke(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        if ($user instanceof User && $user->getTimezone()!=='') {
            try {
                date_default_timezone_set($user->getTimezone());
            } catch (\ErrorException $exception) {
                $this->logger->info('User '.$user->getId().' set timezone '.$user->getTimezone().' failed.',
                    ['caller' =>'TimezoneListener']);
            }
        }
    }
}
