<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Form\Api\IntegrationGalleryType;
use App\Form\Api\IntegrationHtmlType;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class BackendInternalGalleryController extends BackendHelpersController
{
    private $menu = "api";

    /**
     * Wizard to configure an internal image Gallery
     * @Route("/gallery/{uuid}/{intapi_uuid?}", name="b_api_image_gallery")
     */
    public function apiInternalGallery(
        $uuid, $intapi_uuid, Request $request,
        UserApiRepository $userApiRepository,
        IntegrationApiRepository $intApiRepository,
        EntityManagerInterface $entityManager)
    {
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        if (!$api instanceof IntegrationApi) {
            $api = new IntegrationApi();
        }

        $imagePublicPath = $this->getParameter('screen_gallery_directory') . '/' . $this->getUser()->getId().'/'.$api->getId();

        $form = $this->createForm(IntegrationGalleryType::class, $api);
        $form->handleRequest($request);
        $error = "";$preSuccessMsg = "";
        $imageUploaded = false;
        if ($form->getClickedButton()) {
            switch ($form->getClickedButton()->getName()) {

                case 'remove_image':
                    try {
                        $removeFlag = unlink($this->publicRelativePath . $api->getImagePath());
                    } catch (\ErrorException $e) {
                        $this->addFlash('error', "Could not find image. ");
                        $removeFlag = false;
                    }
                    if ($removeFlag) {
                        $api->setImagePath('');
                        $preSuccessMsg = "Image was removed. ";
                    }
                    break;

                case 'remove_html':
                    $this->addFlash('success', "The HTML api integration ".$api->getName()." was removed");
                    $entityManager->remove($api);
                    $entityManager->flush();
                    return $this->redirectToRoute('b_home_apis');
                    break;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            // This condition is needed because the 'imageFile' field is not required
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                // this is needed to safely include the file name as part of the URL. We will allow only one image per HTML API so name does not matter:
                // $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $api->getId() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                $imageUploadPath = $this->publicRelativePath.$imagePublicPath;


                try {
                    $imageUploaded = true;
                    //dump($imageUploadPath,$newFilename);exit();
                    $imageFile->move(
                        $imageUploadPath,
                        $newFilename
                    );

                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $error = $e->getMessage();
                    $imageUploaded = false;
                }

                $api->setImagePath($imagePublicPath.'/'.$newFilename);

            }
            $userApi->setIsConfigured(true);
            $api->setUserApi($userApi);
            try {
                $entityManager->persist($api);
                $entityManager->flush();
            }
            catch (UniqueConstraintViolationException $e) {
                $error = '"'.$api->getName().'" exists already. Please use another name for this HTML element (Name your API)';
                $this->addFlash('error', $error);
            }
            catch (\Exception $e) {
                $error = $e->getMessage();
                $this->addFlash('error', $error);
            }

            if ($error === '') {
                $this->addFlash('success', $preSuccessMsg." HTML content saved");
                return $this->redirectToRoute('b_api_image_gallery',
                    [
                        'uuid' => $userApi->getId(),
                        'intapi_uuid' => $api->getId()
                    ]);
            }
        }
        $render = [
            'title' => 'Upload your gallery images',
            'form' => $form->createView(),
            'intapi_uuid' => $intapi_uuid,
            'userapi_id' => $userApi->getId(),
            'image_path' => $api->getImagePath(),
            'menu' => $this->menu
        ];

        return $this->render(
            'backend/api/conf-gallery.html.twig', $render
        );
    }
}