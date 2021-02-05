<?php
namespace App\Form\Screen;

use App\Entity\Display;
use App\Entity\Screen;
use App\Entity\UserWifi;
use App\Repository\DisplayRepository;
use App\Repository\UserWifiRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
        $hoursFrom = array();
        $hoursTo = array();
        $days = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
        $stDays = array();

        for ($i = 0; $i <= 23; $i++) {
            $hoursFrom["{$i}:00"] = "$i";
            $hoursTo["{$i}:59"]   = "$i";
        }
        for ($i = 1; $i <= 7; $i++) {
            $stDays["$i {$days[$i]}"] = "$i";
        }
        $resolver->setDefaults(
            [
                'templates' => false,
                'user' => null,
                'public' => [
                    "Screen URL is protected with an Authentication token" => 0,
                    "Screen URL is public for anyone knowing the link" => 1
                ],
                'stDays' => $stDays,
                'stHoursFrom' => $hoursFrom,
                'stHoursTo' => $hoursTo,
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
            ->add('userWifi', EntityType::class,
                [
                    'label' => false,
                    'class' => UserWifi::class,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped' => false,
                    'choice_value' => function (?UserWifi $entity) {
                        return $entity ? $entity->getWifiPass() : '';
                    },
                    'query_builder' => function(UserWifiRepository $repo) use($options) {
                        return $repo->wifisForUser($options['user']);
                    },
                    'attr' => ['class' => 'wifi_select','onclick' => 'jsonBlue(this)']
                ])
            ->add('display', EntityType::class,
                [
                    'label' => 'Output display',
                    'class' => Display::class,
                    'required' => true,
                    'placeholder' => 'Optional choose an Eink display',
                    'label_attr' => ['style' => 'margin-top:1em'],
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'query_builder' => function(DisplayRepository $repo) {
                        return $repo->orderByTypeAndSize();
                    }
                ])

            ->add('public', ChoiceType::class, [
                'label' => 'Privacy level',
                'choices' => $options['public'],
                'attr' => ['class' => 'form-control']
            ])

            ->add('outBearer', TextType::class, [
                'label' => 'Bearer token',
                'attr' => ['class' => 'form-control','readonly' => true]
            ])

            ->add('loggingActive', CheckboxType::class, [
                'label' => 'Log every request to this display',
                'required' => false
            ])

            ->add('stDayFrom', ChoiceType::class,
                [
                    'choices' => $options['stDays'],
                    'label' => 'Day from',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('stDayTo', ChoiceType::class,
                [
                    'choices' => $options['stDays'],
                    'label' => 'Day from',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('stHourFrom', ChoiceType::class,
                [
                    'choices' => $options['stHoursFrom'],
                    'label' => 'Hour from',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('stHourTo', ChoiceType::class,
                [
                    'choices' => $options['stHoursTo'],
                    'label' => 'Hour to',
                    'attr' => ['class' => 'form-control']
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save screen',
                    'attr' => ['class' => 'btn btn-primary form-control']
                ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}