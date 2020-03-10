<?php
namespace App\Form\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class IntegrationHtmlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name your Api',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Use a meaningful name to identify it in your list',
                        'class' => 'form-control'
                    ]
                ])

            ->add('html', TextareaType::class,
                [
                    'required' => true,
                    'label' => 'Your HTML Content',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save contents',
                    'attr' => ['class' => 'btn btn-primary form-control', 'style' => 'margin-top:2em']
                ])
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}