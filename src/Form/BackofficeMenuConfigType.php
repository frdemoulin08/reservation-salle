<?php

namespace App\Form;

use App\Entity\BackofficeMenuConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackofficeMenuConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('config', TextareaType::class, [
                'label' => 'Configuration (JSON)',
                'mapped' => false,
                'attr' => [
                    'rows' => 20,
                    'class' => 'w-full rounded-base border border-default bg-neutral-primary-soft p-3 font-mono text-xs',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activer cette configuration',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BackofficeMenuConfig::class,
        ]);
    }
}
