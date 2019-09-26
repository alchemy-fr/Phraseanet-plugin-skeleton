<?php

namespace Alchemy\SkeletonPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('text_setting', TextType::class, [
                'label' => 'A text setting',
                'attr'  => ['class' => 'input-xxlarge']
            ])
            ->add('int_setting', IntegerType::class, [
                'label' => 'An interger setting (4...31)',
                'required' => false,
                'attr' => [
                    'class' => 'input-small',
                    'min' => 4,
                    'max' => 31
                ]
            ])
            ->add('textarea_setting', TextareaType::class, [
                'label' => 'A textarea setting',
                'attr'  => ['class' => 'input-xxlarge', "rows" => 6]
            ]);
    }

    public function getName()
    {
        // return 'skeleton_configuration';
        return 'configuration';
    }
}
