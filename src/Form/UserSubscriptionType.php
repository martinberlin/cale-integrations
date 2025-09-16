<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSubscriptionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paypalEmail', EmailType::class, [
                'label' => 'Paypal Email:',
                'attr' => ['class' => 'form-control']
            ])
            ->add('submit', SubmitType::class,
                [
                    'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:1em']
                ]
            );
    }

    public function getBlockPrefix()
    {
        return "";
    }
}