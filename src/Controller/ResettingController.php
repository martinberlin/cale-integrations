<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\NewPasswordType;
use App\Form\PasswordRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResettingController extends AbstractController
{
    /**
     * @Route("/reset_password", name="reset_password", methods={"GET", "POST"})
     */
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,  \Swift_Mailer $mailer
    ) {
        $form = $this->createForm(PasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailFrom = $this->getParameter('email_reset_password');
            $email = $form->get('email')->getData();
            $token = bin2hex(random_bytes(32));
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                $user->setPasswordRequestToken($token);
                $entityManager->flush();
                $message = (new \Swift_Message($this->getParameter('email_reset_pass_title')))
                    ->setFrom($emailFrom)
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'emails/recover_pass.html.twig',
                            [
                                'token_link' => $this->generateUrl('reset_password_confirm', ['token' => $token ], UrlGeneratorInterface::ABSOLUTE_URL),
                                'host' => $request->getSchemeAndHttpHost()
                            ]
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                $this->addFlash('success', "An email with a reset link has been sent to your address. Please click on the link to reset your password");

                return $this->redirectToRoute('reset_password');
            }
        }

        return $this->render('reset-password.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/reset_password/confirm/{token}", name="reset_password_confirm", methods={"GET", "POST"})
     */
    public function resetPasswordCheck(
        Request $request,
        string $token,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ) {
        $user = $entityManager->getRepository(User::class)->findOneBy(['passwordRequestToken' => $token]);

        if (!$token || !$user instanceof User) {
            $this->addFlash('danger', "User not found");

            return $this->redirectToRoute('reset_password');
        }

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $password = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($password);
            $user->setPasswordRequestToken(null);
            $entityManager->flush();

            $token = new UsernamePasswordToken($user, $password, 'main');
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            $this->addFlash('success', "Your new password has been set. You should be able to login now please try");

            return $this->redirectToRoute('login');
        }

        return $this->render('reset-password-confirm.html.twig', ['form' => $form->createView()]);

    }
}