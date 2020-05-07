<?php
namespace App\Controller;

use App\Form\TerminateType;
use App\Form\UsernameAgreementType;
use App\Form\UserProfileType;
use App\Form\UserSupportType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/backend/user")
 */
class BackendUserController extends AbstractController
{
    private $menu = "user";
    /**
     * @Route("/profile", name="b_user_profile")
     */
    public function userProfileEdit(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $languages = $this->getParameter('api_languages');
        $form = $this->createForm(UserProfileType::class, $user,
            [
                'languages' => array_flip($languages)
            ]);
        // We don't want the username to be changed so is not mapped
        $form->get('name')->setData($user->getName());
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==="") {
                $this->addFlash('success', 'Your user profile is updated');
            }
        }

        return $this->render(
            'backend/user/admin-user-profile.html.twig', [
                'title' => 'Your user profile',
                'form'  => $form->createView(),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/support", name="b_user_support")
     */
    public function support(Request $request, EntityManagerInterface $entityManager,\Swift_Mailer $mailer, BackendController $backendController)
    {
        $maxChars = 2500;
        $emailFrom = $this->getUser()->getEmail();
        $form = $this->createForm(UserSupportType::class, null,
            ['html_max_chars' => $maxChars, 'email_from' => $emailFrom]);
        $form->handleRequest($request);
        $formSubmitted = $form->isSubmitted() && $form->isValid();
        if ($formSubmitted) {
            $title = $form->get('type')->getViewData();
            $body = $form->get('html')->getViewData();
            $cc = $form->get('cc')->getViewData();

            $message = (new \Swift_Message($title))
                ->setFrom($emailFrom)
                ->setTo($this->getParameter('cale_support_email'))
                ->setBody(
                    $this->renderView(
                        'emails/support.html.twig',
                        [
                            'body' => $body,
                            'host' => $request->getSchemeAndHttpHost()
                        ]
                    ),
                    'text/html'
                );

            if ($cc) {
                $message->setCc($emailFrom);
            }

            if ($mailer->send($message, $failures))
            {
                $this->addFlash('success', 'Thanks! Your support Email was sent to CALE. We reply usually in the next 24 hours and we will let you know if we need further information to solve your problem');
            } else {
                $this->addFlash('error', 'Sorry there was an error trying to send this. '.print_r($failures, true).
                    ' Please copy the message and send it to '.$this->getParameter('cale_support_email'));
            }
        }

        return $this->render(
            'backend/user/admin-user-support.html.twig', [
                'title' => 'Get official support from CALE',
                'form'  => $form->createView(),
                'html_max_chars' => $maxChars,
                'form_submitted' => $formSubmitted,
                'isMobile' => $backendController->isMobile($request),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/terminate", name="b_user_terminate")
     */
    public function terminate(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(TerminateType::class, null);
        $form->handleRequest($request);

        $confirm = $form->get('confirm')->getViewData();

        $formSubmitted = $form->isSubmitted() && $form->isValid();
        if ($formSubmitted) {
            if ($confirm) {

                $entityManager->remove($this->getUser());
                $entityManager->flush();

                $session = new Session();
                $session->invalidate();
                $this->addFlash('success', 'Your user account was terminated');
                return $this->redirectToRoute('logout');
            } else {
                $this->addFlash('error', 'Please mark the checkbox if you really want to confirm your account termination');
            }
        }

        return $this->render(
            'backend/user/terminate.html.twig', [
                'title' => 'Terminate my account at CALE',
                'form' => $form->createView(),
                'menu' => $this->menu
                ]
        );
    }

    /**
     * @Route("/api", name="b_user_api")
     */
    public function api(Request $request)
    {

        return $this->render(
            'backend/user/api-qr.html.twig',
            [
                'title' => 'My API key',
                'key' => $this->getUser()->getApiKey(),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/qr/{key}", name="b_user_qr")
     */
    public function qr($key)
    {
        $qrCode = new QrCode($key);
        $response = new Response();
        $response->setContent($qrCode->writeString());
        $response->headers->set('Content-type', $qrCode->getContentType());
        return $response;
    }

    }
