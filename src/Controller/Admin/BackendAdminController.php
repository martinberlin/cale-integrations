<?php

namespace App\Controller\Admin;

use App\Entity\Api;
use App\Entity\Display;
use App\Form\Admin\ApiType;
use App\Form\Admin\DisplayType;
use App\Form\Admin\NewsletterType;
use App\Repository\ApiRepository;
use App\Repository\DisplayRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/backend/admin")
 */
class BackendAdminController extends AbstractController
{

    /**
     * @Route("/admindash", name="b_admin_dashboard")
     */
    public function users(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $users = $userRepository->findBy([], ['lastLogin' => 'DESC']);

        return $this->render(
            'backend/admin/admin-dashboard.html.twig',
            [
                'title' => 'Superadmin dashboard',
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/apis", name="b_admin_apis")
     */
    public function apis(ApiRepository $apiRepository)
    {
        $apis = $apiRepository->findAll();

        return $this->render(
            'backend/admin/apis.html.twig',
            [
                'title' => 'List apis',
                'apis' => $apis
            ]
        );
    }

    /**
     * @Route("/api/edit/{id?}", name="b_admin_api_edit")
     */
    public function apiEdit($id, Request $request, ApiRepository $ApiRepository,
                            EntityManagerInterface $em)
    {
        if (isset($id)) {
            $api = $ApiRepository->find($id);
            if (!$api instanceof Api) {
                throw $this->createNotFoundException("$id is not a valid API id");
            }
            $title = "Editing API $id:".$api->getName();
        } else {
            $api = new Api();
            $title = 'Creating new API';
        }

        $form = $this->createForm(ApiType::class, $api);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($api);
                $em->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if (!isset($error)) {
                $this->addFlash('success', "API saved");
                return $this->redirectToRoute('b_admin_apis');
            }
        }

        return $this->render('backend/admin/api-edit.html.twig', [
            'title' => $title,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/newsletter", name="b_admin_newsletter")
     */
    public function newsletter(Request $request, \Swift_Mailer $mailer, UserRepository $userRepository)
    {
        $maxChars = 2500;
        $emailFrom = $this->getParameter('cale_official_email');
        $emailUser = $this->getUser()->getEmail();
        $form = $this->createForm(NewsletterType::class, null,
            ['html_max_chars' => $maxChars, 'email_from' => $emailFrom, 'test_email' => $emailUser]);
        $form->handleRequest($request);
        $formSubmitted = $form->isSubmitted() && $form->isValid();
        $error = '';

        if ($formSubmitted) {
            $title = $form->get('title')->getViewData();
            $body = $form->get('html')->getViewData();
            $testEmail = $form->get('testEmail')->getViewData();
            $users = $userRepository->findBy(['doNotDisturb' => false]);
            $sentCount = 0;

            foreach ($users as $user) {
                if ($testEmail && $sentCount>0) break;

                $message = (new \Swift_Message($title))
                    ->setFrom($emailFrom)
                    ->setBody(
                        $this->renderView(
                            'emails/newsletter.html.twig',
                            [
                                'body' => $body,
                                'firstname' => $user->getFirstname(),
                                'host' => $request->getSchemeAndHttpHost()
                            ]
                        ),
                        'text/html'
                    );

                if ($testEmail) {
                    $emailTo = $emailUser;
                } else {
                    $emailTo = $user->getEmail();
                }
                $message->setTo($emailTo);

                if (!$mailer->send($message, $failures)) {
                    $this->addFlash('error', 'Sorry there was an error trying to send this to email: '.$emailTo.' Errors:'. print_r($failures, true) .
                        ' Sending aborted here');
                    break;
                }
                $sentCount++;
            }
            if ($error === '') {
               $this->addFlash('success', 'Newsletter was sent to '.$sentCount.' users.');
            }
        }

        return $this->render(
            'backend/admin/newsletter-tool.html.twig', [
                'title' => 'Newsletter tool',
                'form' => $form->createView(),
                'html_max_chars' => $maxChars,
            ]
        );
    }

    /**
     * @Route("/displays", name="b_admin_displays")
     */
    public function displays(DisplayRepository $displayRepository)
    {
        $displays = $displayRepository->findAll();

        return $this->render(
            'backend/admin/displays.html.twig',
            [
                'title' => 'List displays',
                'displays' => $displays
            ]
        );
    }

    /**
     * @Route("/display/edit/{id?}", name="b_admin_display_edit")
     */
    public function displayEdit($id, Request $request, DisplayRepository $displayRepository,
                            EntityManagerInterface $em)
    {
        if (isset($id)) {
            $display = $displayRepository->find($id);
            if (!$display instanceof Display) {
                throw $this->createNotFoundException("$id is not a valid Display id");
            }
            $title = "Editing Display $id:".$display->getName();
        } else {
            $display = new Display();
            $title = 'Creating new Display';
        }

        $form = $this->createForm(DisplayType::class, $display);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($display);
                $em->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if (!isset($error)) {
                $this->addFlash('success', "Display ".$display->getName()." saved with ID ".$display->getId());
                return $this->redirectToRoute('b_admin_displays');
            }
        }

        return $this->render('backend/admin/display-edit.html.twig', [
            'title' => $title,
            'form' => $form->createView()
        ]);
    }

}