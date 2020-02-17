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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    }
                ])
            ->add('placeholder', ChoiceType::class,
                [
                    'label' => 'in',
                    'placeholder' => 'template area:',
                    'required' => true,
                    'choices' => $options['placeholders'],
                    'attr' => ['class' => 'form-control', 'style' => 'margin-bottom:0.4em']
                ])
            ->add('invertedColor', CheckboxType::class,
            [
                'label' => 'Invert B/W colors',
                'value' => 1,
                'required' => false
            ])
            ->add('maxResults', NumberType::class,
                [
                    'label' => 'Max. rows',
                    'required' => true,
                    'attr' => [
                        'size'      =>2,
                        'maxlength' =>1
                    ]
                ])
            ->add('sortPos', NumberType::class,
                [
                    'label' => 'Sort pos.',
                    'required' => true,
                    'attr' => ['size'      =>2,'maxlength' =>1]
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