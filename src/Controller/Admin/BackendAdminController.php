<?php

namespace App\Controller\Admin;

use App\Entity\Api;
use App\Form\Admin\ApiType;
use App\Form\UsernameAgreementType;
use App\Repository\ApiRepository;
use App\Repository\ScreenRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/users", name="b_admin_users")
     */
    public function users(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $users = $userRepository->findAll();

        return $this->render(
            'backend/admin/admin-users.html.twig',
            [
                'title' => 'List users',
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
            $title = 'Editing API $id';
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
            }
        }

        return $this->render('backend/admin/api-edit.html.twig', [
            'title' => $title,
            'form' => $form->createView()
        ]);
    }
}