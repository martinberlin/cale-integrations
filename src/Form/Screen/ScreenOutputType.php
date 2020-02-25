<?php
namespace App\Form\Screen;

use App\Entity\Display;
use App\Entity\Screen;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                    '50 %' => 0.5,
                    '60 %' => 0.6,
                    '70 %' => 0.7,
                    '80 %' => 0.8,
                    '90 %' => 0.9,
                    '100 %' => 1,
                    '110 %' => 1.1
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
                    '2 hours' => 7200
                    ],
                'bitdepth' => [
                    '1 bit' => 1,
                    '4 bit' => 4,
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
                    'label' => 'BMP is catched for',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('outCompressed', CheckboxType::class,
                [
                    'value' => 1,
                    'required' => false,
                    'label' => 'Zlib compressed',
                ])
            ->add('outSsl', CheckboxType::class,
                [
                    'value' => 1,
                    'required' => false,
                    'label' => 'Use HTTPS',
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
                    'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:-0.2em']
                ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}