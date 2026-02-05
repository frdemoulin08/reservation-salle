<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\EquipmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EquipmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'equipment.label.required'),
                ],
            ])
            ->add('equipmentType', EntityType::class, [
                'label' => 'Type d’équipement',
                'class' => EquipmentType::class,
                'choice_label' => 'label',
                'placeholder' => 'Sélectionner un type d’équipement',
                'constraints' => [
                    new NotBlank(message: 'equipment.type.required'),
                ],
                'query_builder' => static fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder('et')
                    ->orderBy('et.label', 'asc'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipment::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'equipment';
    }
}
