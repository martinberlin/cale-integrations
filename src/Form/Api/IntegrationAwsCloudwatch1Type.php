<?php
namespace App\Form\Api;

use App\Entity\UserApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationAwsCloudwatch1Type extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserApi::class,
                'regions' => [
                    'eu-central-1 Frankfurt'  => 'eu-central-1',
                    'eu-west-1 Ireland'       => 'eu-west-1',
                    'eu-west-2 London'        => 'eu-west-2',
                    'eu-west-3 Paris'         => 'eu-west-3',
                    'eu-north-1 Stockholm'    => 'eu-north-1',
                    'us-east-1 N. Virginia'   => 'us-east-1',
                    'us-east-2 Ohio'          => 'us-east-2',
                    'us-west-1 N. California' => 'us-west-1',
                    'us-west-2 Oregon'        => 'us-west-2',
                    'ap-east-1 Hong Kong'     => 'ap-east-1',
                    'ap-south-1 Mumbai'       => 'ap-south-1',
                    'ap-southeast-1 Singapore'=> 'ap-southeast-1',
                    'ap-southeast-2 Sydney'   => 'ap-southeast-2',
                    'ap-northeast-1 Tokyo'    => 'ap-northeast-1',
                    'ap-northeast-2 Seoul'    => 'ap-northeast-2',
                    'ap-northeast-3 Osaka'    => 'ap-northeast-3',
                    'cn-north-1 Beijing'      => 'cn-north-1',
                    'cn-northwest-1 Ningxia'  => 'cn-northwest-1',
                    'me-south-1 Bahrain'      => 'me-south-1',
                    'sa-east-1 Sao Paulo'     => 'sa-east-1',
                ]
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class,
                [
                    'required' => true,
                    'label' => 'Key',
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
            ->add('password', TextType::class,
                [
                    'label' => 'Secret',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Encrypted before persisting',
                        'class' => 'form-control'
                    ]
                ])

            ->add('region', ChoiceType::class,
                [
                    'label' => "AWS Region",
                    'required' => true,
                    "choices" => $options['regions'],
                    'attr' => ['class' => 'form-control']
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save Credentials',
                    'attr' => ['class' => 'btn btn-primary form-control', 'style' => 'margin-top:2em']
                ])
        ;
    }

    public function getBlockPrefix()
    {
        return "";
    }

}