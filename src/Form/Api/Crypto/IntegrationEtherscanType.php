<?php
namespace App\Form\Api\Crypto;

use App\Form\Api\IntegrationApiType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                    'label' => 'ETH full Address including 0x in the start',
                    'required' => true,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'constraints' => [
                        new Length(['min'=>41,'max'=>42])
                    ]
                ])

            ->add('numberOfTransactions', IntegerType::class,
                [
                    'label' => 'Number of transactions to list below balance. Empty to disable',
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'style' => 'width:5em'
                    ]
                ])

            ->add('showConversionPrice', CheckboxType::class,
                [
                    'label' => 'Display conversion price in USD/BTC',
                    'mapped' => false,
                    'required' => false,
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