<?php
namespace App\Form;

use App\Entity\User;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class,
                [
                    'attr' => ['class' => 'form-control']
                ])
            ->add('language', ChoiceType::class,
                [
                    'label' => "Language of preference",
                    "choices" => $options['languages'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('email', EmailType::class,
                [
                    'attr' => ['class' => 'form-control']
                ])
            ->add('plainPassword', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'attr' => ['class' => 'form-control']

                ])

            ->add('captcha', CaptchaType::class, [
              'label' => 'Are you human? Please repeat this characters:'
            ])

            ->add('submit', SubmitType::class,
                [
                    'attr' => ['class' => 'btn btn-primary']
                ]
                );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'languages' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}