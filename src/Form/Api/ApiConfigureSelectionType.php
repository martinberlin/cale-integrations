<?php
namespace App\Form\Api;

use App\Entity\Api;
use App\Entity\UserApi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiConfigureSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('api', EntityType::class,
                [
                    'class' => Api::class,
                    'label' => 'Select Api',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('accessToken', TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Authorization token',
                        'class' => 'form-control',
                        'style' => 'visibility:hidden;margin-top:2em'
                    ]
                ])
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Configure API',
                    'attr' => ['class' => 'btn btn-primary', 'style' => 'margin-top:2em']
                ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserApi::class
            ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}