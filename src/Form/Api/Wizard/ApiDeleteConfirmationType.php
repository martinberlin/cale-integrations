<?php
namespace App\Form\Api\Wizard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiDeleteConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deleteConfirm', CheckboxType::class,
                [
                    'value' => 1,
                    'mapped' => false,
                    'label' => 'I confirm this API will be deleted and also dissapear from my screen configurations',

                ])

            ->add('delete', SubmitType::class,
                [
                    'label' => 'Delete this API configuration',
                    'attr' => ['class' => 'btn btn-danger', 'style' => 'margin-top:1em']
                ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return "";
    }
}