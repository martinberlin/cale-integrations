<?php
namespace App\Form\Api\Sensor;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

class SCD40ExtendedType extends SCD40Type
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Sensor name',
                    'data' => $options['apiName'],
                    'required' => true,
                    'mapped' => false,
                    'attr' => [
                        'placeholder' => 'Use a meaningful name to identify it in your list (Ex. BTCEUR hourly)',
                        'class' => 'form-control'
                    ],
                    'constraints' => array(
                        new Length(array('max' => 130))
                    )
                ])
            ->add('width', NumberType::class,
                [
                    'label' => 'Width',
                    'required' => true,
                    'constraints' => [
                        new Range([
                            'min' => 200,
                            'max' => 2000
                        ])
                    ],
                    'attr' => ['style'=>'width:4em']
                ])
            ->add('height', NumberType::class,
                [
                    'label' => 'Height',
                    'required' => true,
                    'constraints' => [
                        new Range([
                            'min' => 200,
                            'max' => 2000
                        ])
                    ],
                    'attr' => ['style'=>'width:4em']
                ])
            ->add('dataRows', NumberType::class,
                [
                    'label' => 'Number of data rows',
                    'required' => true,
                    'constraints' => [
                        new Range([
                            'min' => 7,
                            'max' => 730
                        ])
                    ]
                ])

            ->add('setDisplayDimensions', ChoiceType::class,
                [
                    'label' => 'Set width and height',
                    'required' => false,
                    'mapped'  => false,
                    "choices" => $options['displays'],
                    'placeholder' => 'Select one display',
                    'attr' => ['onChange'=>'onSetDimension(this)', 'title'=>'Not saved: Just fills automatically width & height']
                ])

            ->add('candleType', ChoiceType::class,
                [
                    'label' => '1st Chart type',
                    'required' => true,
                    "choices" => $options['chartTypes1'],
                ])
            ->add('co2ChartType', ChoiceType::class,
                [
                    'label' => 'CO2 Chart',
                    'required' => true,
                    "choices" => $options['chartTypes2'],
                ])
            ->add('color1', TextType::class,
                [
                    'label' => 'Color Temp.',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control pickr',
                        'maxlength' => '7'
                    ]
                ])
            ->add('color2', TextType::class,
                [
                    'label' => 'Humidity',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control pickr',
                        'maxlength' => '7'
                    ]
                ])
            ->add('color3', TextType::class,
                [
                    'label' => 'CO2',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control pickr',
                        'maxlength' => '7'
                    ]
                ])
            ->add('exclude1', CheckboxType::class,
                [
                    'label' => 'Exclude CO2 (Chart1)',
                    'required' => false
                ])
            ->add('exclude2', CheckboxType::class,
                [
                    'label' => 'Exclude Hum. (Chart1)',
                    'required' => false
                ])
            ->add('showXTickChart1', CheckboxType::class,
                [
                    'label' => 'Show X ticks in Chart1 (Temp,Hum)',
                    'required' => false
                ])
            ->add('showXTickChart2', CheckboxType::class,
                [
                    'label' => 'Show X ticks in Chart2 (CO2)',
                    'required' => false
                ])
            ->add('additionalChartCo2', CheckboxType::class,
                [
                    'label' => 'Show CO2 in Chart2',
                    'required' => false
                ])
            ->add('axisFontFile', ChoiceType::class,
                [
                    'label' => 'Font',
                    'required' => true,
                    "choices" => $options['fontFile'],
                ])
            ->add('axisFontSize', ChoiceType::class,
                [
                    'label' => 'Font size',
                    'required' => true,
                    "choices" => $options['fontSize'],
                ])
            // TELEMETRYHARBOR
            ->add('telemetryActive', CheckboxType::class,
                [
                    'label' => false,
                    'required' => false
                ])
            ->add('telemetryIngestUrl', TextType::class,
                [
                    'label' => 'CARGO1 Api URL',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '150'
                    ]
                ])
            ->add('telemetryApiKey', TextType::class,
                [
                    'label' => 'CARGO1 Api key',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '50'
                    ]
                ])
            ->add('telemetryDevice', TextType::class,
                [
                    'label' => 'CARGO1 device name (ship_id)',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '50'
                    ]
                ])
            ->add('telemetryCargo', ChoiceType::class,
                [
                    'label' => 'CARGO1 type of data',
                    'required' => false,
                    'choices' => [
                        'Temperature' => 'temperature',
                        'Humidity' => 'humidity',
                        'CO2' => 'co2',
                        'Light' => 'light'
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '50'
                    ]
                ])
            ->add('telemetryIngestUrl2', TextType::class,
                [
                    'label' => 'CARGO2 Api URL',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '150'
                    ]
                ])
            ->add('telemetryApiKey2', TextType::class,
                [
                    'label' => 'CARGO2 Api key',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '50'
                    ]
                ])
            ->add('telemetryDevice2', TextType::class,
                [
                    'label' => 'CARGO2 device name (ship_id)',
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '50'
                    ]
                ])
            ->add('telemetryCargo2', ChoiceType::class,
                [
                    'label' => 'CARGO2 type of data',
                    'required' => false,
                    'choices' => [
                        'Temperature' => 'temperature',
                        'Humidity' => 'humidity',
                        'CO2' => 'co2',
                        'Light' => 'light'
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'maxlength' => '50'
                    ]
                ])
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
    /**
     * This form has extra fields because of the summernote HTML editor
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'apiName' => '',
            'chartTypes1' => [
                'Bars' => 'bars',
                'Stacked Bars' => 'stackedbars',
                'Lines' => 'lines',
                'Area' => 'area',
                'Points' => 'points',
            ],
            'chartTypes2' => [
                'Bars' => 'bars',
                'Lines' => 'lines',
                'Area' => 'area',
                'Points' => 'points',
            ],
            'fontSize' => [
                '9' => 9,
                '10' => 10,
                '11' => 11,
                '12' => 12,
            ],
            'fontFile' => [
                'Digital 7'   => 'digital-7.ttf',
                'Benjamin G.' => 'benjamingothic.ttf',
                'Futura'      => 'futura.ttf',
                'Varela'      => 'varela.ttf',
                'IBM plex'    => 'ibm-plex.ttf',
            ],
            'displays' => []
        ]);
    }
}