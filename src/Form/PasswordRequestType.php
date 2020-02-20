<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PasswordRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class,
                [
                    'label' => 'Your login Email:',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('send', SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn btn-primary form-control',
                        'style' => 'margin-top:1em'
                    ]
                ])
            ;
    }
}