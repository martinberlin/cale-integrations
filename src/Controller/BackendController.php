<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Form\Api\ApiConfigureSelectionType;
use App\Form\Api\IntegrationWeatherType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
            'backend/admin-home.html.twig',
            [
                'title' => 'Admin dashboard'
            ]
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
                'title' => 'List users',
                'users' => $users
            ]
        );
    }

    /**
     * @Route("/api/configure", name="b_api_configure")
     */
    public function apiConfigure(Request $request, EntityManagerInterface $entityManager)
    {
        $userApi = new UserApi();
        $form = $this->createForm(ApiConfigureSelectionType::class, $userApi);
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $userApi->setUser($this->getUser());
            try {
                $entityManager->persist($userApi);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error === '') {
                $this->addFlash('success', 'API is connected with your user');
            }
        }

        return $this->render(
            'backend/api/admin-configure-api.html.twig',
            [
                'title' => 'Api configurator. Step 1',
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/api/customize", name="b_api_customize_step2")
     */
    public function apiCustomize(Request $request, EntityManagerInterface $entityManager)
    {
        $languages = $this->getParameter('api_languages');
        $user = $this->getUser();

        $api = new IntegrationApi();
        $form = $this->createForm(IntegrationWeatherType::class, $api,
            [
                'languages' => array_flip($languages)
            ]);
        $form->handleRequest($request);
        $error = "";

        if ($form->isSubmitted() && $form->isValid()) {
            //$api->setUserApi($this->getUser());
            try {
                $entityManager->persist($api);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error === '') {
                $this->addFlash('success', 'API is now customized');
            }
        }

        return $this->render(
            'backend/api/admin-configure-api.html.twig',
            [
                'title' => 'Api configurator. Step 1',
                'form' => $form->createView()
            ]
        );
    }

}
