<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileexistsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('file_exists', [$this, 'fileexists']),
        ];
    }

    public function fileexists($basepath, $path, $filename, $ext)
    {
        if (file_exists($basepath.$path.$filename.$ext)) {
            return $path.$filename.$ext;
        } else {
            return $path."noimage".$ext;
        }
    }
}