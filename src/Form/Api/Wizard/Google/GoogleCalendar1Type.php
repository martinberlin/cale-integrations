<?php
namespace App\Form\Api\Wizard\Google;

use App\Form\Api\IntegrationApiType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class GoogleCalendar1Type extends IntegrationApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('credentialsFile', FileType::class,
                [
                    'label' => 'Upload credentials.csv',
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'style' => 'height:3.2em'
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '20k',
                            'mimeTypes' => [
                                'application/json',
                                'text/plain'
                            ],
                            'mimeTypesMessage' => 'Please upload a valid credentials file',
                        ])
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