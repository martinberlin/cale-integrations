<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserSupportType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $sType = ['General: Please describe how to reproduce it', 'Configuring my APIs','Configuring my Screens','Need a new API renderer', 'Setting up Cloudwatch monitoring', 'Found a bug in [AREA]'];
        $resolver->setDefaults(
            [
                'type' => [
                    $sType[0] => $sType[0],
                    $sType[1] => $sType[1],
                    $sType[2] => $sType[2],
                    $sType[3] => $sType[3],
                    $sType[4] => $sType[4],
                    $sType[5] => $sType[5],
                ],
                'html_max_chars' => null,
                'email_from' => null,
                'allow_extra_fields' => true /* Needed for summernote */
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class,
                [
                    'label' => "Support type",
                    "choices" => $options['type'],
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

            ->add('cc', CheckboxType::class,
                [
                    'required' => false,
                    'label' => "Send a copy also to my email ".$options['email_from'],
                    "value" => 1
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Send support Email',
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