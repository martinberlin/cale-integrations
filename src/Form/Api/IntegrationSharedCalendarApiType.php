<?php
namespace App\Form\Api;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class IntegrationSharedCalendarApiType extends IntegrationApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('calId', TextType::class,
                [
                    'required' => false,
                    'label' => 'Calendar ID',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])

            ->add('timezone', TextType::class,
                [
                    'label' => 'Your timezone',
                    'attr' => [
                        'placeholder' => 'Europe/Berlin',
                        'class' => 'form-control'
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