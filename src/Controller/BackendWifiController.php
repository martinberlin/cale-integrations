<?php
namespace App\Controller;

use App\Entity\UserWifi;
use App\Form\Admin\UserWifiType;
use App\Repository\UserWifiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/backend/wifi")
 */
class BackendWifiController extends AbstractController
{
    private $menu = "wifi";
    /**
     * @Route("/", name="b_wifis")
     */
    public function wifis(Request $request, UserWifiRepository $wifiRepository, BackendController $backendController)
    {
        $wifis = $wifiRepository->findBy(['user'=>$this->getUser()]);
        return $this->render(
            'backend/wifi/index.html.twig',
            [
                'title' => 'My WiFi configurations',
                'wifis' => $wifis,
                'isMobile' => $backendController->isMobile($request),
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/edit/{uuid?}", name="b_wifi_edit")
     */
    public function wifiEdit($uuid, Request $request, UserWifiRepository $wifiRepository,
                               EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        if (is_null($uuid)) {
            $wifi = new UserWifi();
            $wifi->setUser($this->getUser());
            $title = $translator->trans('Add new')." WiFi access point";
        } else {
            $wifi = $wifiRepository->find($uuid);
            $title = $translator->trans('Edit').' screen "'.$wifi->getType().'"';
        }

        $form = $this->createForm(UserWifiType::class, $wifi);
        $form->handleRequest($request);
        $error = '';

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $entityManager->persist($wifi);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if ($error==='') {
                $this->addFlash('success', "WiFi $uuid: ".$wifi->getWifiSsid()." ".$translator->trans('saved'));
                return $this->redirectToRoute('b_wifis');
            }
        }

            return $this->render(
            'backend/wifi/edit.html.twig',
            [
                'title' => $title,
                'form' => $form->createView(),
                'uuid' => $uuid,
                'menu' => $this->menu
            ]
        );
    }

    /**
     * @Route("/delete/{uuid?}", name="b_wifi_delete")
     */
    public function wifiDelete($uuid, UserWifiRepository $wifiRepository, EntityManagerInterface $entityManager)
    {
        $wifi = $wifiRepository->find($uuid);
        if (!$wifi instanceof UserWifi) {
            throw $this->createNotFoundException("$uuid is not a valid WiFi");
        }
        if ($wifi->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException("$uuid is not your WiFi");
        }
        $error = '';
        try {
            $entityManager->remove($wifi);
            $entityManager->flush();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->addFlash('error', $error);
        }
        if ($error==='') {
            $extraMessage = (rand(0,2)===1) ? "Keeping your WiFIs clean?" : "";
            $this->addFlash('success', "Deleted $wifi. $extraMessage");
        }
        return $this->redirectToRoute('b_wifis');
    }

}