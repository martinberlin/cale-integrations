<?php
namespace App\Form\Screen;

use App\Entity\IntegrationApi;
use App\Entity\TemplatePartial;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartialType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // TODO: placeholders should be inserted from controller
        $resolver->setDefaults(
            [
                'placeholders' =>
                    [
                        '1st Column' => 'Column_1st',
                        '2nd Column' => 'Column_2nd',
                    ],
                'data_class' => TemplatePartial::class
            ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('integrationApi', EntityType::class,
                [
                    'label' => 'API',
                    'class' => IntegrationApi::class,
                    'required' => true,
                    'placeholder' => 'Your connected API',
                    /*'label_attr' => ['style' => 'margin-top:1em'],*/
                    'attr' => ['class' => 'form-control']
                ])
            ->add('placeholder', ChoiceType::class,
                [
                    'label' => 'Output in template',
                    'placeholder' => 'Select where',
                    'required' => true,
                    'choices' => $options['placeholders'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('invertedColor', CheckboxType::class,
            [
                'label' => 'Invert B/W colors',
                'value' => 1,
                'required' => false
            ])
            ->add('maxResults', NumberType::class,
                [
                    'label' => 'Max. results',
                    'required' => true,
                    'empty_data' => 1
                ])
            ->add('sortPos', NumberType::class,
                [
                    'label' => 'Sort position',
                    'required' => true,
                    'empty_data' => 1
                ])
            ;
    }

    public function getBlockPrefix()
    {
        return "";
    }
}