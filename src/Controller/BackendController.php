<?php
namespace App\Controller;

use App\Entity\UserApi;
use App\Form\Api\ApiConfigureSelectionType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend")
 */
class BackendController extends AbstractController
{
    /**
     * @Route("/", name="b_home")
     */
    public function home()
    {
        return $this->render(
            'backend/admin-home.html.twig'
        );
    }

    /**
     * @Route("/users", name="b_users")
     */
    public function users(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $users = $userRepository->findAll();

        return $this->render(
            'backend/admin-users.html.twig',
            [
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/api/configure", name="b_api_configure")
     */
    public function apiConfigure(UserRepository $userRepository, Request $request)
    {
        $user = $this->getUser();
        $languages = $this->getParameter('api_languages');

        $userApi = new UserApi();
        $form = $this->createForm(ApiConfigureSelectionType::class, $userApi,
            [
                'languages' => array_flip($languages)
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // todo. persist,etc
        }

        return $this->render(
            'backend/api/admin-configure-api.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
}