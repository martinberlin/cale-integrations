<?php
namespace App\Controller;

use App\Entity\Screen;
use App\Form\Screen\ScreenType;
use App\Repository\DisplayRepository;
use App\Repository\ScreenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/backend/screen")
 */
class BackendScreenController extends AbstractController
{
    /**
     * @Route("/", name="b_screens")
     */
    public function screens(ScreenRepository $screenRepository)
    {
        $screens = $screenRepository->findBy(['user'=>$this->getUser()]);
        return $this->render(
            'backend/admin-screen.html.twig',
            [
                'title' => 'My screens',
                'screens' => $screens
            ]
        );
    }

    /**
     * @Route("/edit/{uuid?}", name="b_screen_edit")
     */
    public function screenEdit($uuid, Request $request, ScreenRepository $screenRepository,EntityManagerInterface $entityManager)
    {
        if (is_null($uuid)) {
            $screen = new Screen();
            $screen->setUser($this->getUser());
            $title = "Add new screen";
        } else {
            $screen = $screenRepository->find($uuid);
            $title = "Edit screen $uuid";
        }

        $form = $this->createForm(ScreenType::class, $screen, ['templates' => $this->getParameter('screen_templates')]);
        $form->handleRequest($request);
        $error = '';

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $entityManager->persist($screen);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==='') {
                $this->addFlash('success', "Screen $uuid saved");
            }
        }

            return $this->render(
            'backend/screen/screen-edit.html.twig',
            [
                'title' => 'My screens',
                'form' => $form->createView()
            ]
        );
    }
}