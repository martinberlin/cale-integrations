<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'dateFormat' => [
                    date('D d.m.Y') => 'D d.m.Y',
                    date('l d.m.Y') => 'l d.m.Y',
                    date('d.m.Y')   => 'd.m.Y',
                    date('d/m/Y')   => 'd/m/Y',
                    date('D d/m/Y') => 'D d/m/Y',
                    date('l d \of F Y') => 'l d \of F Y',
                    date('Y-m-d')   => 'Y-m-d',
                    date('j.n.y')   => 'j.n.y',
                    date('j/M/y')   => 'j/M/y',
                    date('\W\e\e\k W \of Y j.M')        => '\W\e\e\k W \of Y j.M',
                    date('\D\a\y z,\W\e\e\k W \of Y')   => '\D\a\y z,\W\e\e\k W \of Y',
                    date('D \D\a\y z,\W\e\e\k W,Y')     => 'D \D\a\y z,\W\e\e\k W,Y',
                ],
                'hourFormat' => [
                    date('H:i') => 'H:i',
                    date('H:i a') => 'H:i a',
                    date('H:i A') => 'H:i A',
                    date('H:i e') => 'H:i e',
                    date('H:i e') => 'H:i T',
                    ],
                'timezonesPreferred' => [
                    'Europe/Berlin','Europe/London','Europe/Amsterdam','Europe/Paris','Europe/Rome',
                    'Europe/Madrid','Europe/Lisbon','Europe/Athens','Europe/Brussels','Europe/Monaco','Europe/Zurich',
                    'Europe/Stockholm','Europe/Copenhagen','Europe/Helsinki','Europe/Oslo',
                    'Europe/Prague','Europe/Sofia','Europe/Vienna',
                    'Europe/Warsaw','Europe/Andorra','Europe/Budapest','Europe/Dublin','Europe/Moscow','Europe/Kiev',
                ],
                'languages' => null,
                'data_class' => User::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Username',
                'attr' => ['class' => 'form-control', 'readonly' => true]
            ])
            ->add('email', EmailType::class,
                [
                    'label' => 'Email used for login',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('language', ChoiceType::class,
                [
                    'label' => "Language of preference",
                    "choices" => $options['languages'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('timezone', TimezoneType::class, [
                'label' => 'Your timezone',
                'attr' => ['class' => 'form-control'],
                'preferred_choices' => $options['timezonesPreferred']
            ])
            ->add('dateFormat', ChoiceType::class, [
                'label' => 'Your preferred date format',
                'choices' => $options['dateFormat'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('hourFormat', ChoiceType::class, [
                'label' => 'Hour format',
                'choices' => $options['hourFormat'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('submit', SubmitType::class,
                [
                    'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:1em']
                ]
            )
            ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}