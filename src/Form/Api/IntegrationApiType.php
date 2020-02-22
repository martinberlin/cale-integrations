<?php
namespace App\Form\Api;

use App\Entity\IntegrationApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Base type for the other integrated APIs
class IntegrationApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $grayColor = '#A9A9A9';
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

            ->add('jsonSettings', TextareaType::class,
                [
                    'label' => 'Customized json settings (Can be left as is)',
                    'required' => false,
                    'label_attr' => ['style'=>"color:$grayColor"],
                    'attr' => [
                        'class' => 'form-control',
                        'rows'  => 2
                    ]
                ])
            ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => IntegrationApi::class,
                'languages' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}