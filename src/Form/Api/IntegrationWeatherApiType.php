<?php
namespace App\Form\Api;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class IntegrationWeatherType extends IntegrationApiType
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
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}