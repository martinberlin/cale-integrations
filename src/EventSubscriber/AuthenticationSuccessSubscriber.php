<?php
// src/EventSubscriber/LocaleSubscriber.php
namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private $security;
    private $logger;
    private $em;

    public function __construct(Security $security, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->logger = $logger;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        //AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event )
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            try {
                $user->setLastLogin(new \DateTime());
                $this->em->persist($user);
                $this->em->flush();
            } catch (\Exception $exception) {
                $this->logger->error('User '.$user->getId().' could not persist LastLogin date. '.$exception->getMessage(),
                    ['caller' =>'AuthenticationSuccessListener']);
            }
        }
    }
}
