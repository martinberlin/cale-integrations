<?php
namespace App\Controller;

use App\Entity\IntegrationApi;
use App\Entity\UserApiGalleryImage;
use App\Form\Api\IntegrationGalleryType;
use App\Form\Api\IntegrationHtmlType;
use App\Repository\IntegrationApiRepository;
use App\Repository\UserApiGalleryImageRepository;
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
        UserApiGalleryImageRepository $userGalleryRepository,
        EntityManagerInterface $entityManager)
    {
        $userApi = $this->getUserApi($userApiRepository, $uuid);
        $api = $this->getIntegrationApi($intApiRepository, $intapi_uuid);
        if (!$api instanceof IntegrationApi) {
            $api = new IntegrationApi();
        }
        $lastImage = $userGalleryRepository->getLastImageData($this->getUser(),$api);
        $nextImageId = $lastImage['imageId']+1;
        $imgPosition = ($lastImage['position']===-1)?$lastImage['position']+1:$lastImage['position']+10;
        $imgExtension = '';
        $imgKilobytes = 0;
        $imgCaption = '';
        //dump($lastImage, $nextImageId,$nextImagePosition);exit();

        $imagePublicPath = $this->getParameter('screen_images_directory') . '/' . $this->getUser()->getId().'/'.$api->getId();
        $form = $this->createForm(IntegrationGalleryType::class, $api,
        ['position' => $imgPosition]);
        $form->handleRequest($request);
        $error = "";$messageSuccess = "";
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
                        $messageSuccess = "Image was removed. ";
                    }
                    break;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imgCaption = $form->get('caption')->getData();
            $imgPosition = $form->get('position')->getData();
            $imageFile = $form->get('imageFile')->getData();
            // This condition is needed because the 'imageFile' field is not required
            if ($imageFile) {
                $imgKilobytes = round($imageFile->getSize()/1000);
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $imgExtension = $imageFile->guessExtension();
                $newFilename = $nextImageId . '.' . $imgExtension;
                $imageUploadPath = $this->publicRelativePath.$imagePublicPath;
                try {
                    $imageUploaded = true;
                    $imageFile->move(
                        $imageUploadPath,
                        $newFilename
                    );
                    $messageSuccess = "Image was uploaded";

                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $error = $e->getMessage();
                    $imageUploaded = false;
                    $messageSuccess = "Error uploading image: ".$error;
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

            // Add image to Gallery
            if ($imageUploaded) {
                $image = new UserApiGalleryImage();
                $image->setUser($this->getUser());
                $image->setIntApi($api);
                $image->setImageId($nextImageId);
                $image->setPosition($imgPosition);
                $image->setExtension($imgExtension);
                $image->setKb($imgKilobytes);
                $image->setCaption($imgCaption);
                $userGalleryRepository->saveImage($image);
            }

            if ($error === '') {
                $this->addFlash('success', $messageSuccess);
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
            'gallery_path' => $imagePublicPath,
            'gallery_images' => $userGalleryRepository->getGalleryImages($this->getUser(),$api),
            'menu' => $this->menu
        ];

        return $this->render(
            'backend/api/conf-gallery.html.twig', $render
        );
    }
}