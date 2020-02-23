<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', TextType::class,
                [
                'attr' => ['class' => 'form-control']
                ]
            )
            ->add('_password', PasswordType::class,
                [
                    'attr' => ['class' => 'form-control']
                ])
            ->add('submit', SubmitType::class,
                [
                    'label' => 'submit_login',
                    'attr' => ['class' => 'btn btn-primary form-control']
                ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}