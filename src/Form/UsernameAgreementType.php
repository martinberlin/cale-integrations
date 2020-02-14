<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsernameAgreementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Username',
                'attr' => ['class' => 'form-control']
            ])
            ->add('confirm', CheckboxType::class,
                [
                    'value' => 1,
                    'mapped' => false,
                    'required' => false,
                    'label' => 'I understand and agree with this terms',
                ])

            ->add('declineAction', SubmitType::class,
                [
                    'label' => "I don't agree with this please delete my account",
                    'attr' => ['class' => 'btn btn-danger form-control', 'style' => 'margin-top:1em']
                ])

            ->add('confirmAction', SubmitType::class,
                [
                    'label' => 'Create my account',
                    'attr' => ['class' => 'btn btn-success form-control', 'style' => 'margin-top:1em']
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
                'data_class' => User::class
            ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}