<?php
namespace App\Form\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class IntegrationHtmlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name your Api',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Use a meaningful name to identify it in your list',
                        'class' => 'form-control'
                    ]
                ])

            ->add('html', TextareaType::class,
                [
                    'required' => false,
                    'label' => 'Your HTML Content',
                    'attr' => [
                        'class' => 'form-control summernote'
                    ]
                ])

            ->add('imageFile', FileType::class,
                [
                    'label' => 'Upload an image',
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '100k',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/bmp',
                                'image/png',
                                'image/gif',
                                'image/svg+xml'
                            ]
                        ])
                    ]
                ])
            ->add('imagePosition', ChoiceType::class,
                [
                    'choices' => [
                        'left' => 'left',
                        'center' => 'center',
                        'right' => 'right',
                    ],
                    'label' => 'Image position',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('imageType', ChoiceType::class,
                [
                    'choices' => [
                        'background' => 'background',
                        'floating' => 'float'
                    ],
                    'label' => 'Image type',
                    'attr' => ['class' => 'form-control']
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save contents',
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
     * But even with this will still run basic integrity checks, for example whether an uploaded file was too large or whether non-existing fields were submitted.
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => false,
        ]);
    }
}