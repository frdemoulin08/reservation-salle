<?php

namespace App\Form;

use App\Service\PhotoUploadHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhotoUploadType extends AbstractType
{
    public function __construct(private readonly PhotoUploadHelper $photoUploadHelper)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => true,
                'multiple' => true,
                'attr' => [
                    'accept' => implode(',', PhotoUploadHelper::MIME_TYPES),
                ],
                'constraints' => $this->photoUploadHelper->buildFormConstraints(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
