<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ) {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Your account was created");
            $token = new UsernamePasswordToken($user, $password, 'main');
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirectToRoute('thanks', [], 302);
        }

        return $this->render(
            'register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}