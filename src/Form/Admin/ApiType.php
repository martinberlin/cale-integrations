<?php
namespace App\Form\Admin;

use App\Entity\Api;
use App\Entity\ApiCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Api::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', NumberType::class, [
                'label' => 'ID',
                'attr' => ['class' => 'form-control']
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('urlName', TextType::class, [
                'label' => 'Url name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('jsonRoute', TextType::class,
                [
                    'label' => 'Renderer route',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('url', TextType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('responseType', TextType::class,
                [
                    'attr' => ['class' => 'form-control']
                ])
            ->add('authNote', TextType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ->add('category', EntityType::class,
                [
                    'label' => 'Category',
                    'class' => ApiCategory::class,
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
            ->add('editRoute', TextType::class,
                [
                    'label' => 'Important: Controller route that will edit this API',
                    'required' => true,
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