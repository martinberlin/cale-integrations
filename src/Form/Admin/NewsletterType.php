<?php
namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsletterType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'html_max_chars' => null,
                'email_from' => null,
                'test_email' => null,
                'allow_extra_fields' => true /* Needed for summernote */
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class,
                [
                    'label' => "Title",
                    'required' => true,
                    'attr' => ['class' => 'form-control']
                ])

            ->add('html', TextareaType::class,
                [
                    'required' => false,
                    'label' => 'HTML Content',
                    'attr' => [
                        'class' => 'form-control summernote'
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new Length(['max' => $options['html_max_chars']])
                    ]
                ])
            ->add('testEmail', CheckboxType::class,
                [
                    'required' => false,
                    'label' => "Send only one test email to ".$options['test_email'],
                    "value" => 1
                ])
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Send Newsletter',
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