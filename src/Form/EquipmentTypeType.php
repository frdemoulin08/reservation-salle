<?php

namespace App\Form;

use App\Entity\EquipmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EquipmentTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isCodeDisabled = (bool) $options['code_disabled'];

        $builder
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'equipment_type.label.required'),
                ],
            ])
            ->add('code', TextType::class, [
                'label' => 'Code',
                'empty_data' => '',
                'disabled' => $isCodeDisabled,
                'constraints' => $isCodeDisabled
                    ? []
                    : [
                        new NotBlank(message: 'equipment_type.code.required'),
                        new Regex(
                            pattern: '/^[A-Z]+(_[A-Z]+)*$/',
                            message: 'equipment_type.code.format'
                        ),
                    ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EquipmentType::class,
            'code_disabled' => false,
        ]);
        $resolver->setAllowedTypes('code_disabled', 'bool');
    }
}
