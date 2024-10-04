<?php
namespace App\Controller;

use App\Entity\PaymentLog;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Mdb\PayPal\Ipn\InputStream;
use Mdb\PayPal\Ipn\Listener;
use Mdb\PayPal\Ipn\MessageFactory\InputStreamMessageFactory;
use Mdb\PayPal\Ipn\Service\GuzzleService;
use Mdb\PayPal\Ipn\Verifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/paypal")
 */
class IpnController extends AbstractController
{
    /**
     * @Route("/ipn", name="paypal_ipn")
     */
    public function paypalIpn(Request $request, EntityManagerInterface $entityManager)
    {
        $service = new GuzzleService(
            new Client(),
            'https://ipnpb.paypal.com/cgi-bin/webscr'
        );
        $verifier = new Verifier($service);

        $messageFactory = new InputStreamMessageFactory(new InputStream());

        $listener = new Listener(
            $messageFactory,
            $verifier,
            new EventDispatcher()
        );

        $paymentLog = new PaymentLog();
        $paymentLog->setArrayStorage([1=>'none']);
        $paymentLog->setNotes($request->getContent());
        $entityManager->persist($paymentLog);
        $entityManager->flush();
        $response = new Response();
        $response->setContent("OK");
        return $response;
    }
}