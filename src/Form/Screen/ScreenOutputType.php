<?php
namespace App\Form\Screen;

use App\Entity\Display;
use App\Entity\Screen;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScreenOutputType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'ditherOptions' => [
                    'Random'        => '_random',
                    'Checks'        => 'checks',
                    '2x2 ordered'   => 'o2x2',
                    '3x3 ordered'   => 'o3x3',
                    '4x4 ordered'   => 'o4x4',
                    '8x8 ordered'   => 'o8x8',
                    '4x4 halftone angled' => 'h4x4a',
                    '6x6 halftone angled' => 'h6x6a',
                    '8x8 halftone angled' => 'h8x8a',
                    '4x4 halftone orthogonal' => 'h4x4o',
                    '6x6 halftone orthogonal' => 'h6x6o',
                    '8x8 halftone orthogonal' => 'h8x8o',
                    '16x16 halftone orthogonal' => 'h16x16o',
                    '5x5 Circles white' => 'c5x5w',
                    '5x5 Circles black' => 'c5x5b',
                    '6x6 Circles white' => 'c6x6w',
                    '6x6 Circles black' => 'c6x6b',
                ],
                'brightness' => [
                    '90 %' => 90,
                    '100 % default' => 100,
                    '110 %' => 110,
                    '120 %' => 120,
                    '130 %' => 130,
                    '140 %' => 140,
                    '150 %' => 150,
                    '170 %' => 170,
                    ],
                'zoom' => [
                    '40 %' => '0.40',
                    '50 %' => '0.50',
                    '60 %' => '0.60',
                    '65 %' => '0.65',
                    '70 %' => '0.70',
                    '75 %' => '0.75',
                    '80 %' => '0.80',
                    '85 %' => '0.85',
                    '90 %' => '0.90',
                    '95 %' => '0.95',
                    '98 %' => '0.98',
                    '100 %' => '1.00',
                    '105 %' => '1.05',
                    '110 %' => '1.10',
                    '120 %' => '1.20',
                    '130 %' => '1.30',
                    '140 %' => '1.40',
                ],
                'cache_seconds' => [
                    '2 secs' => 2,
                    '30 secs' => 30,
                    '1 minute' => 60,
                    '2 minutes' => 120,
                    '3 minutes' => 180,
                    '5 minutes'  => 300,
                    '10 minutes' => 600,
                    '30 minutes' => 1800,
                    '1 hour' => 3600,
                    '2 hours' => 7200,
                    '6 hours' => 21600,
                    '12 hours'=> 43200
                    ],
                'bitdepth' => [
                    '1 bit' => 1,
                    '4 bit' => 4,
                    '8 bit gray' => 8,
                    '24 TFT' => 24,
                    ],
                'data_class' => Screen::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('outBrightness', ChoiceType::class,
                [
                    'choices' => $options['brightness'],
                    'label' => 'Brightness level',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('outZoomFactor', ChoiceType::class,
                [
                    'choices' => $options['zoom'],
                    'label' => 'Zoom factor',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('outCacheSeconds', ChoiceType::class,
                [
                    'choices' => $options['cache_seconds'],
                    'label' => 'cache_seconds',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('outCompressed', CheckboxType::class,
                [
                    'value' => 1,
                    'required' => false,
                    'label' => 'Zlib',
                    'attr' => ['title' => 'Zlib compressed is just experimental and for BMP']
                ])
            ->add('outJpegCompression', RangeType::class,
                [
                    'required' => false,
                    'label' => 'Compression quality (JPG)',
                    'attr' => [
                        'maxlength' => 2,
                        'min' => 50,
                        'max' => 99
                    ]
                ])
            ->add('outSsl', CheckboxType::class,
                [
                    'value' => 1,
                    'required' => false,
                    'label' => 'Use HTTPS',
                ])
            ->add('imgDither', CheckboxType::class,
                [
                    'value' => 1,
                    'required' => false,
                    'label' => 'Enable dither',
                ])
            ->add('imgDitherOptions', ChoiceType::class,
                [
                    'choices' => $options['ditherOptions'],
                    'placeholder' => 'Dither options',
                    'required' => false,
                    'label' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('outBitDepth', ChoiceType::class,
                [
                    'choices' => $options['bitdepth'],
                    'label' => 'Bitsdepth',
                    'attr' => ['class' => 'form-control']
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save settings',
                    'attr' => ['class' => 'btn btn-primary']
                ])
            ->add('submitLeft', SubmitType::class,
                [
                    'label' => 'Save settings',
                    'attr' => ['class' => 'btn btn-primary']
                ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}