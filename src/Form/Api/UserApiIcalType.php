<?php
namespace App\Form\Api;

use App\Entity\UserApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserApiIcalType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserApi::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'required' => true,
                    'mapped' => false,
                    'label' => 'Name:',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])

            ->add('resourceUrl', TextType::class,
                [
                    'required' => true,
                    'label' => 'iCal URL',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])

            ->add('username', TextType::class,
                [
                    'required' => true,
                    'label' => 'Username',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])

            ->add('password', PasswordType::class,
                [
                    'label' => 'Password',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Encrypted before persisting',
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