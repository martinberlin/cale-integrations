<?php
namespace App\Controller;

use App\Entity\Display;
use App\Entity\PaymentLog;
use App\Form\ble\UploadType;
use App\Repository\ApiRepository;
use App\Repository\DisplayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class HomeController
 * @package App\Controller
 * This Routes are not working with annotations so are defined in routes.yaml
 */
class HomeController extends AbstractController
{
    protected $publicRelativePath = '../public';
    // This Routes are defined in routes.yaml
    public function index(Request $request)
    {

        return $this->render(
            $request->getLocale().'/www-index.html.twig'
        );
        //return $this->redirectToRoute('register', [], 301);
    }

    public function aboutCale(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/about/www-about-cale.html.twig',
            ['title' => $translator->trans('nav_about')]
        );
    }

    public function aboutEthereum(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/about/ethereum-powered.html.twig',
            ['title' => $translator->trans('nav_ethereum')]
        );
    }

    public function threeDModels(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/about/3d-models.html.twig',
            ['title' => $translator->trans('nav_3d')]
        );
    }

    public function tftDisplays(Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $displays = $displayRepository->findBy(['type' => 'tft'],['width' => 'DESC']);
        return $this->render(
            $request->getLocale().'/display/www-tft.html.twig',
            [
                'displays' => $displays,
                'title' => $translator->trans('title_displays_tft')
            ]
        );
    }

    public function apis(Request $request, ApiRepository $apiRepository, TranslatorInterface $translator)
    {
        $apis = $apiRepository->findAll();
        return $this->render(
            $request->getLocale().'/api/www-apis.html.twig',
            [
                'apis' => $apis,
                'title' => $translator->trans('nav_apis')
            ]
        );
    }

    public function transparently(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-built-transparently.html.twig',
            ['title' => $translator->trans('nav_transparently_built')]
        );
    }

    public function thanks(Request $request)
    {
        return $this->render(
            $request->getLocale().'/thanks.html.twig'
        );
    }

    public function impressum(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-impressum.html.twig',
            ['title' => $translator->trans('nav_legal')]
        );
    }

    public function privacyPolicy(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/privacy/www-privacy-policy.html.twig',
            ['title' => $translator->trans('nav_privacy')]
        );
    }

    public function googlePrivacyPolicy(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/privacy/google-privacy-policy.html.twig',
            ['title' => $translator->trans('nav_google_privacy')]
        );
    }

    public function architecture(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/www-server-architecture.html.twig',
            ['title' => $translator->trans('nav_server_architecture')]
        );
    }

    public function pricing(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/www-service-pricing.html.twig',
            ['title' => $translator->trans('nav_pricing')]
        );
    }

    public function faq(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/www-faq.html.twig',
            ['title' => $translator->trans('nav_faq')]
        );
    }
    public function getStarted(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/www-get-started.html.twig',
            ['title' => $translator->trans('nav_get_started')]
        );
    }

    public function firmware(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware.html.twig',
            ['title' => $translator->trans('title_firmware')]
        );
    }

    public function firmwareIdf(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-idf.html.twig',
            ['title' => $translator->trans('title_firmware_idf')]
        );
    }


    public function firmwareTft(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-tft.html.twig',
            ['title' => $translator->trans('title_firmware_tft')]
        );
    }

    public function firmwareT5(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-t5.html.twig',
            ['title' => $translator->trans('title_firmware_t5')]
        );
    }

    public function firmwareBlue(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/firmware/www-firmware-blue.html.twig',
            ['title' => $translator->trans('title_firmware_blue')]
        );
    }

    public function apiIcal(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/api/www-api-ical.html.twig',
            ['title' => $translator->trans('title_ical')]
        );
    }

    public function cloudwatch(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/api/www-api-aws-cloudwatch.html.twig',
            ['title' => $translator->trans('title_cloudwatch')]
        );
    }

    public function news(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/news/news.html.twig',
            ['title' => 'News']
        );
    }

    public function serviceEpapersForSale(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/service/epapers-for-sale.html.twig',
            ['title' => $translator->trans('nav_epapers_for_sale')]
        );
    }

    public function cryptoAdvertising(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/advertising/youhodler.html.twig',
            ['title' => $translator->trans('nav_crypto_adv')]
        );
    }

    public function demoDisplay(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/demos/display.html.twig',
            ['title' => $translator->trans('nav_demo_display')]
        );
    }

    public function demoDigitalArt(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/demos/digital-art.html.twig',
            ['title' => $translator->trans('nav_demo_display')]
        );
    }
    /**
     * Good idea but does not work because of locale lang. switch
     * @deprecated Don't use as is
     * @param Request $request
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function serve(Request $request, $page)
    {
        return $this->render(
            $request->getLocale().'/'.$page.'.html.twig'
        );
    }

    public function einkDisplays(Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $displaysList = $displayRepository->findBy(['type' => 'eink'],['width' => 'DESC']);
        $displays = array();
        // Cut only short description
        foreach ($displaysList as $d) {
            $pos = strpos($d->getHtmlDescription(), "<ld>");

            $shortDescription = ($pos === false) ? $d->getHtmlDescription() : substr($d->getHtmlDescription(), 0, $pos);
            if (is_null($shortDescription)) $shortDescription = '';
            $d->setHtmlDescription($shortDescription);
            $displays[] = $d;
        }
        return $this->render(
            $request->getLocale().'/display/www-eink.html.twig',
            [
                'displays' => $displays,
                'title' => $translator->trans('nav_displays')
            ]
        );
    }

    public function einkLanding($brand, $id, Request $request, DisplayRepository $displayRepository, TranslatorInterface $translator)
    {
        $display = $displayRepository->findOneBy(['type' => 'eink', 'brand' => $brand, 'id' => $id]);
        if ($display instanceof Display === false) throw new NotFoundHttpException('Display #'.$id.' not found');
        $brand = str_replace('_', ' ', $display->getBrand());
        return $this->render(
            $request->getLocale().'/display/www-eink-landing.html.twig',
            [
                'd' => $display,
                'title' => $translator->trans('epaper_from').' '.$brand.' '.$display->getName()
            ]
        );
    }

    public function candlestickCharts(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/api/www-api-candlesticks.html.twig',
            ['title' => $translator->trans('nav_api_crypto')]
        );
    }

    public function productCinwrite(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/product/cinwrite.html.twig',
            ['title' => $translator->trans('nav_pcb_cinwrite')]
        );
    }

    public function productC3Epaper(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/product/c3-controller-24fpc.html.twig',
            ['title' => $translator->trans('nav_pcb_c3_24')]
        );
    }
    public function productEpdiy(Request $request, TranslatorInterface $translator)
    {
        return $this->render(
            $request->getLocale().'/product/epdiy-controller.html.twig',
            ['title' => 'epdiy parallel controller']
        );
    }

    /**
     * Slugifies a filename for safe server upload.
     *
     * @param string $filename Original uploaded filename
     * @return string Sanitized, slugified filename
     */
    private function slugify_uploaded_filename($filename)
    {
        // Separate the file extension
        $ext = '';
        $pos = strrpos($filename, '.');
        if ($pos !== false) {
            $ext = substr($filename, $pos + 1);
            $filename = substr($filename, 0, $pos);
        }

        // Convert to UTF-8, strip invalid chars
        $filename = mb_convert_encoding($filename, 'UTF-8', mb_list_encodings());

        // Replace spaces and underscores with dashes
        $slug = preg_replace('/[\s_]+/', '-', $filename);

        // Remove all non-safe characters (keep alphanumeric and dashes)
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);

        // Remove duplicate dashes and trim dashes from ends
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure not empty
        if (empty($slug)) {
            $slug = 'file';
        }

        // Re-attach the extension (lowercase, sanitized)
        if (!empty($ext)) {
            // Only allow a-z, 0-9 in extension
            $ext = strtolower(preg_replace('/[^a-z0-9]/', '', $ext));
            if (!empty($ext)) {
                $slug .= '.' . $ext;
            }
        }

        return $slug;
    }

    /**
     * Corrects orientation of a JPEG image using EXIF data.
     * @param string $filename Path to the JPEG image file
     * @param resource $image GD image resource (created with imagecreatefromjpeg)
     * @return resource Rotated/flipped image resource
     */
    private function fix_image_orientation($filename, $image)
    {
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($filename);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 2: // Mirror horizontally
                        imageflip($image, IMG_FLIP_HORIZONTAL);
                        break;
                    case 3: // Rotate 180°
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 4: // Mirror vertically
                        imageflip($image, IMG_FLIP_VERTICAL);
                        break;
                    case 5: // Mirror horizontally and rotate 270°
                        imageflip($image, IMG_FLIP_HORIZONTAL);
                        $image = imagerotate($image, 270, 0);
                        break;
                    case 6: // Rotate 90°
                        $image = imagerotate($image, -90, 0);
                        break;
                    case 7: // Mirror horizontally and rotate 90°
                        imageflip($image, IMG_FLIP_HORIZONTAL);
                        $image = imagerotate($image, -90, 0);
                        break;
                    case 8: // Rotate 270°
                        $image = imagerotate($image, -270, 0);
                        break;
                    // case 1: Normal - do nothing
                }
            }
        }
        return $image;
    }

    /**
     * Resize an image using PHP-GD to fit within 1872x1404, preserving aspect ratio.
     * Converts unknown formats to JPG.
     * @param string $src_path Path to the uploaded image
     * @param string $dest_path Where to save the resized image (the extension will be forced to .jpg if converted)
     * @param int $jpg_quality JPEG quality for output (1-100), default 90
     * @return bool True on success, false on failure
     */
    private function resize_image_gd($src_path, $dest_path, $target_width, $target_height, $jpg_quality = 60)
    {
        // Get image info and type
        $img_info = getimagesize($src_path);
        if (!$img_info) return false;
        list($orig_width, $orig_height, $img_type) = $img_info;

        // Calculate new dimensions while preserving aspect ratio
        $ratio = min($target_width / $orig_width, $target_height / $orig_height);
        $new_width = (int)($orig_width * $ratio);
        $new_height = (int)($orig_height * $ratio);

        // Create image resource from source
        $convert_to_jpg = false;
        switch ($img_type) {
            case IMAGETYPE_JPEG:
                $src_img = imagecreatefromjpeg($src_path);
                // Correct orientation based on EXIF **only** for JPEG
                $src_img = $this->fix_image_orientation($src_path, $src_img);
                break;
            case IMAGETYPE_PNG:
                $src_img = imagecreatefrompng($src_path);
                break;
            case IMAGETYPE_GIF:
                $src_img = imagecreatefromgif($src_path);
                break;
            default:
                // Any other known/unknown format: try to load and convert to JPEG
                $src_img = imagecreatefromstring(file_get_contents($src_path));
                $convert_to_jpg = true;
        }
        if (!$src_img) return false;

        // Create the destination image
        $dst_img = imagecreatetruecolor($new_width, $new_height);

        // If PNG/GIF, preserve transparency
        if ($img_type == IMAGETYPE_PNG || $img_type == IMAGETYPE_GIF) {
            imagecolortransparent($dst_img, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        // Resample
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);

        // Save to file: if not a known type, or we wish to force conversion, save as .jpg
        if ($convert_to_jpg) {
            // Make sure destination uses .jpg extension
            $dest_path = preg_replace('/\.\w+$/', '.jpg', $dest_path);
            $result = imagejpeg($dst_img, $dest_path, $jpg_quality);
        } else {
            switch ($img_type) {
                case IMAGETYPE_JPEG:
                    $result = imagejpeg($dst_img, $dest_path, $jpg_quality);
                    break;
                case IMAGETYPE_PNG:
                    $result = imagepng($dst_img, $dest_path);
                    break;
                case IMAGETYPE_GIF:
                    $result = imagegif($dst_img, $dest_path);
                    break;
                default:
                    // Fallback to jpg
                    $result = imagejpeg($dst_img, $dest_path, $jpg_quality);
            }
        }
        // Free memory
        imagedestroy($src_img);
        imagedestroy($dst_img);

        return $result;
    }

    public function screenBleJpg(Request $request) {
        // Target dimensions: In the future we could make a Dropdown to allow more displays
        $target_width = 1872;
        $target_height = 1404;

        $form = $this->createForm(UploadType::class);
        $form->handleRequest($request);
        // Get current year and month
        $year = date('Y');   // e.g. 2025
        $month = date('m');  // e.g. 09
        $imgDir = $this->getParameter('screen_images_directory').'/ble/'.$year.'/'.$month.'/';
        $fileUploaded = false;
        $imageCompression = 60;

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageCompression = $form->get('jpgCompression')->getData();

            if ($imageFile) {
                // this is needed to safely include the file name as part of the URL
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugify_uploaded_filename($originalFilename);
                $safeFilename.= '.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->publicRelativePath.$imgDir,
                        $safeFilename
                    );
                    // Resize the original image and save it to the target directory
                    $this->resize_image_gd(
                        $this->publicRelativePath.$imgDir.'/'.$safeFilename,
                        $this->publicRelativePath.$imgDir.'res_'.$safeFilename,
                        $target_width,
                        $target_height,
                        $imageCompression
                    );
                    unlink($this->publicRelativePath.$imgDir.'/'.$safeFilename);

                } catch (\Exception $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('danger', 'Error uploading file: '.$e->getMessage());
                    $fileUploaded = false;
                }

                $fileUploaded = true;
            }
        }
        // www image patch
        $protocol = $request->isSecure() ? 'http' : 'http';
        if ($fileUploaded) {
            $jpgUrl = "$protocol://".$request->getHost().$imgDir.'res_'.$safeFilename;
        } else {
            $jpgUrl = "$protocol://".$request->getHost().'/assets/ble/empty.jpg';
        }
        $jpg = file_get_contents($jpgUrl);

        // Convert that bytes
        $hexStr = bin2hex($jpg);
        $image_size = strlen($jpg);
        $hexImgArray = str_split($hexStr,2);

        return $this->render(
            'ble/screen-ble-jpg.html.twig', [
            'form' => $form->createView(),
            'image_bytes' => implode("", $hexImgArray), // image byte array
            'image_size'  => $image_size,
            'jpgUrl' => $jpgUrl,
            'title' => 'Cliente BLE'
        ]);

    }
}