<?php
namespace App\Controller;

use App\Entity\Screen;
use App\Form\Screen\ScreenType;
use App\Repository\DisplayRepository;
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
    public function screens()
    {
        return $this->render(
            'backend/admin-screen.html.twig',
            [
                'title' => 'My screens'
            ]
        );
    }

    /**
     * @Route("/edit/{uuid?}", name="b_screen_edit")
     */
    public function screenEdit($uuid, Request $request)
    {
        if (is_null($uuid)) {
            $screen = new Screen();
        }

        $form = $this->createForm(ScreenType::class, $screen,
           [
            'templates' => $this->getParameter('screen_templates')
            ]);
        $form->handleRequest($request);
        $error = "";

        return $this->render(
            'backend/screen/screen-edit.html.twig',
            [
                'title' => 'My screens',
                'form' => $form->createView()
            ]
        );
    }
}