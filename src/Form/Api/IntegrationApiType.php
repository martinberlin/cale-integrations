<?php
namespace App\Form\Api;

use App\Entity\IntegrationApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name your Api',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Ex. weather in my city',
                        'class' => 'form-control'
                    ]
                ])
            ->add('language', ChoiceType::class,
                [
                    'label' => "Language of preference",
                    "choices" => $options['languages'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('jsonSettings', TextType::class,
                [
                    'label' => 'Customized json settings',
                    'attr' => [
                        'placeholder' => '{"json":"true"}',
                        'class' => 'form-control'
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