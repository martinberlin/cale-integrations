<?php
namespace App\Form\ble;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFile', FileType::class,
                [
                    'label' => 'Seleccionar imagen (max. %max_kb% Kb)',
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
                                'image/webp',
                                'image/jpeg',
                                'image/bmp',
                                'image/png',
                                'image/gif',
                                'image/svg+xml'
                            ]
                        ])
                    ]
                ])
            ->add('submit', SubmitType::class,
                [
                    'label' => '1. Subir imagen',
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
            'max_size' => '9000', // Kb
            'position' => null,
            'img_caption' => null
        ]);
    }
}