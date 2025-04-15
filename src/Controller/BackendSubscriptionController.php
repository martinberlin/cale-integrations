<?php
namespace App\Controller;

use App\Form\UserSubscriptionType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/backend/subscription")
 */
class BackendSubscriptionController extends AbstractController
{
    private $menu = "subscription";

    /**
     * @Route("/", name="b_subcription")
     */
    public function index(UserRepository $userRepository)
    {
        return $this->render(
            'backend/subscription/index.html.twig',
            [
                'title' => 'My CALE Subscription',
                'user' => $this->getUser(),
                'subscription' => $userRepository->hasSubscription($this->getUser()),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/paypal", name="b_paypal")
     */
    public function paypal(UserRepository $userRepository, Request $request,
                           EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserSubscriptionType::class, $user);
        $form->handleRequest($request);
        $error = '';

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==='') {
                $this->addFlash('success', "Paypal Email ".$translator->trans('saved'));
                return $this->redirectToRoute('b_subcription');
            }
        }

        return $this->render(
            'backend/subscription/paypal.html.twig',
            [
                'title' => 'My Paypal settings',
                'user' => $user,
                'subscription' => $userRepository->hasSubscription($this->getUser()),
                'form' => $form->createView(),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/paypal_agreement", name="b_paypal_agreement")
     */
    public function paypalAgreement(UserRepository $userRepository, Request $request, Payum $payum) {

        $gatewayName = 'paypal';
        $user = $this->getUser();
        $storage = $payum->getStorage('App\Entity\Payment');

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(300); // 3.00 EUR
        $payment->setDescription('CALE Monthly subscription');
        $payment->setClientId($user->getId());
        $payment->setClientEmail($user->getUsername());
        $storage->update($payment);

        $captureToken = $payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'b_paypal_agreement_done' // the route to redirect after capture
        );
        dump($captureToken);exit();
        return $this->redirectToRoute('b_paypal_agreement_done'); //$captureToken->getTargetUrl()

        return $this->render(
            'backend/subscription/index.html.twig',
            [
                'title' => 'Paypal agreement',
                'user' => $this->getUser(),
                'subscription' => $userRepository->hasSubscription($this->getUser()),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/paypal_agreement_done", name="b_paypal_agreement_done")
     */
    public function paypalAgreementDone(UserRepository $userRepository, Request $request) {
        return $this->render(
            'backend/subscription/index.html.twig',
            [
                'title' => 'Paypal agreement accepted',
                'user' => $this->getUser(),
                'subscription' => $userRepository->hasSubscription($this->getUser()),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/paypal_payum_capture_do", name="payum_capture_do")
     */
    public function payumCapture(Request $request) {
        dump($request);exit();
        return $this->render(
            'backend/subscription/index.html.twig',
            [
                'title' => 'Paypal agreement accepted',
                'user' => $this->getUser(),
                'subscription' => $userRepository->hasSubscription($this->getUser()),
                'menu' => $this->menu
            ]
        );
    }


}