<?php
namespace App\Form\Api;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class IntegrationWeatherApiType extends IntegrationApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('latitude', TextType::class,
                [
                    'label' => 'Latitude',
                    'attr' => [
                        'placeholder' => '52.520008',
                        'class' => 'form-control'
                    ]
                ])

            ->add('longitude', TextType::class,
                [
                    'label' => 'Longitude',
                    'attr' => [
                        'placeholder' => '13.404954',
                        'class' => 'form-control'
                    ]
                ])

            ->add('units', ChoiceType::class,
                [
                    'label' => "Units",
                    "choices" => [
                        'Metric (Degrees C°)'   => 'metric',
                        'Imperial (Fahrenheit)' => 'imperial'
                    ],
                    'attr' => ['class' => 'form-control']
                ])

            ->add('language', ChoiceType::class,
                [
                    'label' => "Language of preference",
                    "choices" => $options['languages'],
                    'attr' => ['class' => 'form-control']
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Configure API',
                    'attr' => ['class' => 'btn btn-primary form-control', 'style' => 'margin-top:2em']
                ])
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}