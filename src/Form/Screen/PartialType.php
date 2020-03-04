<?php
namespace App\Form\Screen;

use App\Entity\IntegrationApi;
use App\Entity\Screen;
use App\Entity\TemplatePartial;
use App\Repository\IntegrationApiRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PartialType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // TODO: placeholders should be inserted from controller
        $resolver->setDefaults(
            [
                'data_class' => TemplatePartial::class,
                'placeholders' =>
                    [
                        '1st Column' => 'Column_1st',
                        '2nd Column' => 'Column_2nd',
                        '3rd Column' => 'Column_3rd',
                    ],
                'screen' => false
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /* List only APIs integrated by this user */
            ->add('integrationApi', EntityType::class,
                [
                    'label' => 'show',
                    'class' => IntegrationApi::class,
                    'required' => true,
                    'placeholder' => 'content from:',
                    'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:0.4em'],
                    'query_builder' => function(IntegrationApiRepository $repo) use ( $options ) {
                        return $repo->QueryApisForUser($options['screen']->getUser());
                    },
                    'constraints' => [
                        new NotBlank()
                    ]
                ])
            ->add('placeholder', ChoiceType::class,
                [
                    'label' => 'template area:',
                    'required' => true,
                    'choices' => $options['placeholders'],
                    'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:0.4em'],
                    'constraints' => [
                        new NotBlank()
                        ]
                ])
            ->add('invertedColor', CheckboxType::class,
            [
                'label' => 'Invert B/Text',
                'value' => 1,
                'required' => false
            ])
            ->add('foregroundColor', TextType::class,
                [
                    'label' => 'Text color',
                    'empty_data' => '#000000',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control pickr',
                        'maxlength' => '7'
                    ]
                ])
            ->add('backgroundColor', TextType::class,
                [
                    'label' => 'Background',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control pickr',
                        'maxlength' => '7'
                    ]
                ])

            ->add('maxResults', NumberType::class,
                [
                    'label' => 'Max. rows',
                    'required' => true,
                    'attr' => [
                        'size'      =>3,
                        'maxlength' =>1
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Range(['min' => 1, 'max' => 20])
                        ]
                ])
            ->add('sortPos', NumberType::class,
                [
                    'label' => 'Sort pos.',
                    'required' => true,
                    'attr' => [
                        'size'      =>3,
                        'maxlength' =>1
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Range(['min' => 1, 'max' => 20])
                    ]
                ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $e) {
            if (!$e->getData()) {
                // In create new mode
                $e->getForm()
                    ->add('maxResults', NumberType::class, [
                        'data' => 1,
                        'required' => true,
                        'attr' => ['size'      =>2,'maxlength' =>1]
                    ])
                    ->add('sortPos', NumberType::class, [
                        'data' => 1,
                        'required' => true,
                        'attr' => ['size'      =>2,'maxlength' =>1]
                    ])
                ;
            }
        });
    }

    public function getBlockPrefix()
    {
        return "";
    }
}