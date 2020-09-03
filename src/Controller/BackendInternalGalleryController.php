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
     * Internal function that returns useful information to render Gallery information and control max sizes
     * @param UserApiGalleryImageRepository $userGalleryRepository
     * @param IntegrationApi $api
     * @return mixed
     */
    private function galleryInfo(UserApiGalleryImageRepository $userGalleryRepository, IntegrationApi $api) {
        $gallery['images'] = $userGalleryRepository->getGalleryImages($this->getUser(),$api);
        $gallery['kb_used'] = 0;
        foreach ($gallery['images'] as $i) {
            $gallery['kb_used']+=$i['kb'];
        }
        $gallery['kb_max'] = $this->getParameter('gallery_max_size_total');
        $gallery['kb_left'] = $gallery['kb_max']-$gallery['kb_used'];
        $gallery['kb_percentage_used'] = round(($gallery['kb_used']/$gallery['kb_max'])*100);
        return $gallery;
    }

    /**
     * Wizard to configure an internal image Gallery
     * @Route("/gallery/{uuid}/{intapi_uuid?}/{image_id?}", name="b_api_image_gallery")
     */
    public function apiInternalGallery(
        $uuid, $intapi_uuid, $image_id, Request $request,
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
        $imgPosition = ($lastImage['position']===-1)?$lastImage['position']+1:$lastImage['position']+10;
        $imageEditMode = false;
        if (is_null($image_id) === false) {
            $image = $userGalleryRepository->findOneBy([
                'user'=>$this->getUser(),'intApi'=>$api,'imageId'=>$image_id]);
            $imgPosition = $image->getPosition();
            $imageEditMode = true;
        } else {
            $image = new UserApiGalleryImage();
        }
        $nextImageId = $lastImage['imageId']+1;
        $imgExtension = '';
        $imgKilobytes = 0;
        $imgCaption = $image->getCaption();
        // Check that max. size is not reached
        $maxUploadSize = $this->getParameter('gallery_max_size');
        $gallery = $this->galleryInfo($userGalleryRepository,$api);
        // If size is arriving to limit, reduce upload Kb limit:
        if ($gallery['kb_left'] < $maxUploadSize) {
            $maxUploadSize = $gallery['kb_left'];
        }
        //dump($lastImage, $nextImageId,$nextImagePosition);exit();

        $imagePublicPath = $this->getParameter('screen_images_directory') . '/' . $this->getUser()->getId().'/'.$api->getId();
        $form = $this->createForm(IntegrationGalleryType::class, $api,
            [
            'position' => $imgPosition,
            'img_caption' => $imgCaption,
            'max_size' => $maxUploadSize
            ]);
        $form->handleRequest($request);
        $error = "";
        $messageSuccess = "Image was updated";
        $imageUploaded = false;

        // Delete image action
        if ($request->get('delete')==='1') {
            $imageDeletePath = $this->publicRelativePath.$imagePublicPath.'/'.$image->getImageId().'.'.$image->getExtension();
                    try {
                        $removeFlag = unlink($imageDeletePath);
                    } catch (\ErrorException $e) {
                        $this->addFlash('error', "Could not find image. ");
                        $removeFlag = false;
                    }
                    if ($removeFlag) {
                        $api->setImagePath('');
                        $entityManager->remove($image);
                        $entityManager->flush();
                        $messageSuccess = "Image was removed. ";
                        return $this->redirectToRoute('b_api_image_gallery',
                            [
                                'uuid' => $userApi->getId(),
                                'intapi_uuid' => $api->getId()
                            ]);
                    }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imgCaption = $form->get('caption')->getData();
            $imgPosition = $form->get('position')->getData();
            $imageFile = $form->get('imageFile')->getData();

            // This condition is needed because the 'imageFile' field is not required
            if ($imageFile) {
                $imgKilobytes = round($imageFile->getSize()/1000);
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
                $image->setImageId($nextImageId);
                $image->setIntApi($api);
                $image->setUser($this->getUser());
                $image->setExtension($imgExtension);
                $image->setKb($imgKilobytes);
            }
            if ($imageUploaded || $imageEditMode) {
                // If no new file uploaded only this should change
                $image->setPosition($imgPosition);
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
        // Calculate gallery sizes
        $gallery = $this->galleryInfo($userGalleryRepository,$api);

        $render = [
            'title' => 'Upload your gallery images',
            'form' => $form->createView(),
            'intapi_uuid' => $intapi_uuid,
            'userapi_id' => $userApi->getId(),
            'image_id' => $image_id,
            'image_path' => $api->getImagePath(),
            'gallery_path' => $imagePublicPath,
            'gallery' => $gallery,
            'menu' => $this->menu
        ];
        //dump($render);exit();
        return $this->render(
            'backend/api/conf-gallery.html.twig', $render
        );
    }
}