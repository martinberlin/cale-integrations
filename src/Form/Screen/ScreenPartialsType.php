<?php
namespace App\Form\Screen;

use App\Entity\Screen;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScreenPartialsType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Screen::class,
                'screen' => false,
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('partials', CollectionType::class,
                [
                    'label' => false,
                    'entry_type'    => PartialType::class,
                    'entry_options' => [
                        'label' => false,
                        'screen' => $options['screen']
                    ],
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference' => false,

                    'required' => false,
                    'attr' => [
                        'class' => 'hidden'
                    ]
                ])

            ->add('submit', SubmitType::class,
                [
                    'label' => 'Save screen partials',
                    'attr' => ['class' => 'btn btn-primary']
                ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}