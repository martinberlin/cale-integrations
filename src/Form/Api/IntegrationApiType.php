<?php
namespace App\Form\Api;

use App\Entity\Api;
use App\Entity\IntegrationApi;
use App\Entity\UserApi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Configure API',
                    'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:2em']
                ]);
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