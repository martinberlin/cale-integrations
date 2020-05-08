<?php
namespace App\Form\Admin;

use App\Entity\ShippingTracking;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ShippingTracking::class,
                'sent_by' =>
                [   'Hermes' => 'hermes',
                    'Post.de' => 'post.de',
                ],
                'statuses' =>
                [
                    'In preparation' => 'in_preparation',
                    'Shipped' => 'shipped',
                    'Received' => 'received',
                    'Returned to sender' => 'returns_to_sender'
                ],
                'countries' =>
                [
                    'USA'     => 'us',
                    'Spain'   => 'es',
                    'Germany' => 'de',
                    'France'  => 'fr',
                    'Portugal'      => 'pt',
                    'Great Britain' => 'gb',
                    'Belgium' => 'be',
                    'Holland' => 'nl',
                    'Italy'   => 'it',
                    'Rusia'   => 'ru',
                    'Poland'  => 'pl',
                    'Croatia' => 'hr',
                    'Austria' => 'at',
                ]
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class,
                [
                    'label' => 'User',
                    'class' => User::class,
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => $options['statuses'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('sentBy', ChoiceType::class,
                [
                    'label' => 'Sent by',
                    'choices' => $options['sent_by'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('tracking', TextType::class,
                [
                    'label' => 'Tracking ID',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('countryCode', ChoiceType::class,
                [
                    'label' => 'Country code (2 chars)',
                    'choices' => $options['countries'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('description', TextareaType::class,
                [
                    'label' => 'What was sent',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('costShip', TextType::class,
                [
                    'label' => 'cost Shippping',
                    'attr' => [
                        'placeholder' => '1.00',
                        'class' => 'form-control'
                    ]
                ])
            ->add('costHardware', TextType::class,
                [
                    'label' => 'cost Hardware',
                    'attr' => [
                        'placeholder' => '1.00',
                        'class' => 'form-control'
                    ]
                ])
            ->add('costManufacturing', TextType::class,
                [
                    'label' => 'cost Manufacturing',
                    'attr' => [
                        'placeholder' => '1.00',
                        'class' => 'form-control'
                    ]
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