<?php
namespace App\Controller;

use App\Entity\User;

use App\Form\UserSubscriptionType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

}