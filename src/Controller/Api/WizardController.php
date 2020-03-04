<?php

namespace App\Controller\Api;

use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use App\Form\Api\UserApiIcalType;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use Doctrine\ORM\EntityManagerInterface;
use ICal\ICal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend/api")
 */
class WizardController extends AbstractController
{


    /**
     * @Route("/ical/preview/{intapi_uuid}", name="b_api_preview_ical")
     */
    public function icalPreview($intapi_uuid,IntegrationApiRepository $intApiRepository) {
        $intApi = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        $api = $intApi->getUserApi();
        $error = "";
        $response = new Response();
        try {
            $ical = new ICal();
            $ical->initUrl($api->getResourceUrl(),
                $username = $api->getUsername(), $password = $api->getPassword(),
                $userAgent = null);
            $events = $ical->eventsFromInterval('1 week');
        } catch (\Exception $e) {
            $error = "Could not access iCal. ".$e->getMessage();
            $response->setContent($error);
            return $response;
        }
        $html = '';$count = 0;
        if (isset($events)) {
            $html .= '<h4>&nbsp; Events in the next 7 days</h4>';
        foreach ($events as $event) {
            $dateStart = ($ical->iCalDateToDateTime($event->dtstart));
            $dateEnd = ($ical->iCalDateToDateTime($event->dtend));
            $status = ($event->status == 'CONFIRMED') ? '<span style="color:green">' . $event->status . '</span>' : $event->status;
            $dtstart = $ical->iCalDateToDateTime($event->dtstart_array[3]);
            $summary = $event->summary . ' - '.$dtstart->format('D d.m H:i');
            $html .= '<div class="col-md-3">
                <div class="thumbnail">
                    <div class="caption">
                        <h3>'.$summary.'</h3>';
            $html .= "<h4>$status</h4>
                      <h4>". $dateStart->format('l d.m.Y H:i')." to ".$dateEnd->format('H:i')."</h4>
                    </div>
                </div>
            </div>";
            if ($count > 1 && $count % 3 === 0) {
                $html .= '</div><div class="row">';
            }
            $count++;
        }
        }
        $response->setContent($html);
        return $response;
    }

    /**
     * @Route("/ical/{uuid}/{intapi_uuid?}", name="b_api_wizard_cale-ical")
     */
    public function ical(
        $uuid, $intapi_uuid, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        $form = $this->createForm(UserApiIcalType::class, $userApi);
        if ($api->getName()) {
            $form->get('name')->setData($api->getName());
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($userApi);
                $userApi->setIsConfigured(true);
                $api->setUserApi($userApi);
                $api->setName($form->get('name')->getData());
                $entityManager->persist($api);
                $entityManager->flush();
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }
            if (!isset($error)) {
                $this->addFlash('success', "Saved");
                return $this->redirectToRoute('b_api_wizard_cale-ical',
                    ['uuid' => $userApi->getId(), 'intapi_uuid' => $api->getId()]);
            }
        }
        return $this->render(
            'backend/api/ical.html.twig', [
                'title' => 'Configure access to iCal',
                'form' => $form->createView(),
                'intapi_uuid' => $intapi_uuid,
                'userapi_id'  => $userApi->getId()
            ]
        );
    }

    /*

    */
    /** Helpers to avoid repeating this calls in all the APIs */
    private function getIntegrationApi(IntegrationApiRepository $intApiRepository, $intapi_uuid) {
        $api = new IntegrationApi();
        if (!is_null($intapi_uuid)) {
            $api = $intApiRepository->findOneBy(['uuid' => $intapi_uuid]);
        }
        if (!$api instanceof IntegrationApi) {
            throw $this->createNotFoundException("$intapi_uuid is not a valid integration API");
        }
        return $api;
    }

    private function getUserApi(UserApiRepository $userApiRepository, $uuid) {
        $userApi = $userApiRepository->findOneBy(['uuid'=>$uuid]);
        if (!$userApi instanceof UserApi){
            throw $this->createNotFoundException("$uuid is not a valid API definition");
        }
        if ($userApi->getUser() !== $this->getUser()){
            throw $this->createNotFoundException("You don't have access to API $uuid");
        }
        return $userApi;
    }
}