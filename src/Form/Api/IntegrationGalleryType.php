<?php
namespace App\Form\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class IntegrationGalleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'label' => 'Name your Gallery',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Use a meaningful name to identify it in your list',
                        'class' => 'form-control'
                    ],
                    'constraints' => array(
                        new Length(array('max' => 130))
                    )
                ])
            ->add('imageFile', FileType::class,
                [
                    'label' => 'Upload a new image (max. %max_kb% Kb)',
                    'label_translation_parameters' => [
                      '%max_kb%' => $options['max_size']
                    ],
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => $options['max_size'].'k',
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
            ->add('caption', TextType::class,
                [
                    'label' => 'Image caption',
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'placeholder' => 'Photo credits',
                        'class' => 'form-control',
                        'maxlength' => 140
                    ],
                    'data' => $options['img_caption'],
                    'constraints' => array(
                        new Length(array('max' => 140))
                    )
                ])
            ->add('position', NumberType::class,
                [
                    'label' => 'Sort position',
                    'required' => true,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'data' => $options['position'],
                    'constraints' => array(
                        new Length(array('max' => 3))
                    )
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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'max_size' => '500k',
            'position' => null,
            'img_caption' => null
        ]);
    }
}