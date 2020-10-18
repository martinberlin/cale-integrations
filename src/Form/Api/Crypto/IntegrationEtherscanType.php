<?php
namespace App\Form\Api\Crypto;

use App\Form\Api\IntegrationApiType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class IntegrationEtherscanType extends IntegrationApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('address', TextType::class,
                [
                    'label' => 'Ethereum address',
                    'required' => true,
                    'mapped' => false,
                    'attr' => [
                        'placeholder' => '0x65b7ef685e5b493603740310a84268c6d59f58b5',
                        'class' => 'form-control'
                    ],
                    'constraints' => [
                        new Length(['min'=>42,'max'=>42])
                    ]
                ])

            ->add('numberOfTransactions', IntegerType::class,
                [
                    'label' => 'Number of transactions listed',
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'style' => 'width:9em'
                    ]
                ])

            ->add('showConversionPrice', CheckboxType::class,
                [
                    'label' => 'Display conversion price in USD/BTC',
                    'mapped' => false,
                    'required' => false,
                ])
            ->add('actionQuery', ChoiceType::class,
                [
                    'label' => 'Action query',
                    'required' => true,
                    'mapped' => false,
                    'choices' => [
                        'Balance' => 'balance',
                        'Transactions' => 'txlist'
                    ]
                ])
            ->add('imagePath', ChoiceType::class,
                [
                    'label' => 'Ether icon',
                    'required' => false,
                    'choices' => [
                        '♦ sm gray' => 'eth_sm.svg',
                        '♦ sm red' => 'eth_sm_red.svg',
                        '♦ sm yellow' => 'eth_sm_yel.svg',
                        '♦ s gray' => 'eth_s.svg',
                        '♦ M gray' => 'eth_m.svg',
                        '♦ XS gray' => 'eth_xs.svg',
                        '♦ XL gray' => 'eth_xl.svg',
                        '♦ XL red' => 'eth_xl_red.svg',
                        '♦ XXL red' => 'eth_xxl_red.svg',
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'style' => 'width:9.3em'
                    ]
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Configure API',
                    'attr' => ['class' => 'btn btn-primary form-control', 'style' => 'margin-top:2em']
                ])

            // Overwriting this from IntegrationApiType
            ->add('name', TextType::class,
                [
                    'label' => 'Name your Api',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'My main ETH Account',
                        'class' => 'form-control'
                    ]
                ])

            ->add('jsonSettings', TextareaType::class,
                [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'readonly' => true,
                        'rows'  => 3
                    ]
                ])
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}