<?php
namespace App\Controller;

use App\Entity\Screen;
use App\Form\Screen\ScreenPartialsType;
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
    public function screenEdit($uuid, Request $request, ScreenRepository $screenRepository, EntityManagerInterface $entityManager)
    {
        if (is_null($uuid)) {
            $screen = new Screen();
            $screen->setUser($this->getUser());
            $title = "Add new screen";
        } else {
            $screen = $screenRepository->find($uuid);
            $title = 'Edit screen "'.$screen->getName().'"';
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
                'title' => $title,
                'form' => $form->createView(),
                'uuid' => $uuid
            ]
        );
    }

    /**
     * @Route("/partials/{uuid?}", name="b_screen_partials")
     */
    public function screenPartialsEdit($uuid, Request $request, ScreenRepository $screenRepository, EntityManagerInterface $entityManager)
    {
       $screen = $screenRepository->find($uuid);
        if (!$screen instanceof Screen) {
            throw $this->createNotFoundException("$uuid is not a valid screen");
        }
        if ($screen->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("$uuid is not your screen");
        }

        $title = 'Manage partials for screen "'.$screen->getName().'"';

        $form = $this->createForm(ScreenPartialsType::class, $screen,
            [
                'screen' => $screen
            ]);
        $form->handleRequest($request);
        $error = '';

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($screen->getPartials() as $partial) {
                $partial->setScreen($screen);
            }
            try {
                $entityManager->persist($screen);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==='') {
                $this->addFlash('success', "Partials for screen $uuid saved");
            }
        }
        $display = $screen->getDisplay();
        $screenData = [
            'template_twig' => str_replace('.html.twig','',$screen->getTemplateTwig()),
            'display_width' => $display->getWidth(),
            'display_height' => $display->getHeight(),
            'class_name' => $display->getClassName()
        ];
        return $this->render(
            'backend/screen/screen-partials.html.twig',
            [
                'title' => $title,
                'form' => $form->createView(),
                'screen' => $screenData,
                'uuid'     => $uuid
            ]
        );
    }
}