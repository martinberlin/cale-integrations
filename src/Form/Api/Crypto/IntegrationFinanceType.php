<?php
namespace App\Form\Api\Crypto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class IntegrationFinanceType
 * @package App\Form\Api\Crypto
 * NOTE: I want to make this one different. Instead of being mapped to IntegrationAPI
 * should be mapped to UserAPIFinancialChart directly
 * So name will be non-mapped but all the rest should be mapped to the entity
 */
class IntegrationFinanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name your stock chart',
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
            ->add('candleType', ChoiceType::class,
                [
                    'label' => 'Candlestick type',
                    'required' => true,
                    "choices" => $options['candleTypes'],
                ])
            ->add('symbol', ChoiceType::class,
                [
                    'label' => 'Symbol',
                    'required' => true,
                    "choices" => $options['symbols'],
                ])
            ->add('timeseries', ChoiceType::class,
                [
                    'label' => 'Timeseries',
                    'required' => true,
                    "choices" => $options['timeseries'],
                ])
            ->add('colorAscending', ChoiceType::class,
                [
                    'label' => 'Color of ascending candle',
                    'required' => true,
                    "choices" => $options['colors'],
                ])
            ->add('colorDescending', ChoiceType::class,
                [
                    'label' => 'Color of descending candle',
                    'required' => true,
                    "choices" => $options['colors'],
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save settings and refresh preview',
                    'attr' => ['class' => 'btn btn-primary form-control', 'style' => 'margin-top:2em']
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
            'candleTypes' => [
                'Filled candles' => 'candlesticks2',
                'Hollow candles' => 'candlesticks'
            ],
            'symbols' => [
                'Bitcoin/Euro'  => 'BTCEUR',
                'Ethereum/Euro' => 'ETHEUR',
                'Litecoin/Euro' => 'LTCEUR',
                'Bitcoin/Dollar'  => 'BTCUSD',
                'Ethereum/Dollar' => 'BTCUSD',
                'Litecoin/Dollar' => 'BTCUSD',
            ],
            'timeseries' => [
                'Daily' => 'd',
                'Hourly' => '1h'
            ],
            'colors' => [
                'Red' => 'red',
                'Green' => 'green',
                'Black' => 'black',
                'Gray' => 'gray',
            ]
        ]);
    }
}