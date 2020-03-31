<?php
namespace App\Form\Admin;

use App\Entity\Display;
use App\Entity\UserWifi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserWifiType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserWifi::class,
                    'tags' =>
                [   'Home' => 'Home',
                    'Office 1' => 'Office_1',
                    'Office 2' => 'Office_2',
                    'Mobile hotspot' => 'Hotspot',
                    'Custom 1' => 'Custom_1',
                    'Custom 2' => 'Custom_2',
                ]
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Tag',
                'choices' => $options['tags'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('wifiSsid', TextType::class,
                [
                    'label' => 'WiFi name',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('wifiPass', TextType::class,
                [
                    'label' => 'WiFi password',
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