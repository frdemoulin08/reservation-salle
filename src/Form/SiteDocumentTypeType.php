<?php

namespace App\Form;

use App\Entity\SiteDocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SiteDocumentTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'required' => true,
                'disabled' => $isEdit,
                'constraints' => [
                    new NotBlank(message: 'Le code est obligatoire.'),
                ],
            ])
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Le libellé est obligatoire.'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'required' => false,
                'empty_data' => '0',
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Visible côté front',
                'required' => false,
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Obligatoire',
                'required' => false,
            ])
            ->add('isMultipleAllowed', CheckboxType::class, [
                'label' => 'Multiple autorisé',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SiteDocumentType::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
