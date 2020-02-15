<?php
namespace App\Form\Screen;

use App\Entity\Display;
use App\Entity\Screen;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScreenType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /* templates is injected from services.yml */
        $resolver->setDefaults(
            [
                'templates' => false,
                'data_class' => Screen::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Screen name',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Office Calendar',
                        'class' => 'form-control'
                    ]
                ])

            ->add('templateTwig', ChoiceType::class,
                [
                    'choices' => $options['templates'],
                    'label' => 'Design template',
                    'placeholder' => 'Choose a template',
                    'label_attr' => ['style' => 'margin-top:1em'],
                    'attr' => ['class' => 'form-control']
                ])

            ->add('display', EntityType::class,
                [
                    'label' => 'Output display',
                    'class' => Display::class,
                    'required' => false,
                    'placeholder' => 'Optional choose an Eink display',
                    'label_attr' => ['style' => 'margin-top:1em'],
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}