<?php
namespace App\Form\Admin;

use App\Entity\Display;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Display::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', NumberType::class, [
                'label' => 'ID',
                'attr' => ['class' => 'form-control']
            ])
            ->add('className', TextType::class, [
                'required' => false,
                'label' => 'className',
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', ChoiceType::class,
                [
                    'choices' => [
                        'Eink' => 'eink',
                        'TFT'  => 'tft'
                    ],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('brand', TextType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('width', NumberType::class,
                [
                    'required' => true,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('height', NumberType::class,
                [
                    'required' => true,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('grayLevels', NumberType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])

            ->add('activeSize', TextType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])

            ->add('timeOfRefresh', TextType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('manualUrl', TextType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('purchaseUrl', TextType::class,
                [
                    'required' => false,
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