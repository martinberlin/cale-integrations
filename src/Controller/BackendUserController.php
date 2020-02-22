<?php
namespace App\Controller;

use App\Form\UsernameAgreementType;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/backend/user")
 */
class BackendUserController extends AbstractController
{
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
            'backend/admin-user-profile.html.twig', [
                'title' => 'Your user profile',
                'form'  => $form->createView()
            ]
        );
    }

    }
