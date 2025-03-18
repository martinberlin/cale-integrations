<?php
namespace App\Form\Api\Sensor;

use App\Form\Api\IntegrationApiType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class SCD40Type extends IntegrationApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Sensor name',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Office meeting room',
                        'class' => 'form-control'
                    ],
                    'constraints' => [
                        new Length(['min'=>3,'max'=>42])
                    ]
                ])
            ->add('jsonSettings', TextareaType::class,
                [
                    'label' => 'Copy / paste the data demo',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'rows'  => 2
                    ]
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