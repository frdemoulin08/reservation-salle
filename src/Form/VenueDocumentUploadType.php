<?php

namespace App\Form;

use App\Entity\SiteDocumentType;
use App\Entity\VenueDocument;
use App\Repository\SiteDocumentTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class VenueDocumentUploadType extends AbstractType
{
    public function __construct(private readonly SiteDocumentTypeRepository $documentTypeRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $documentTypes = $options['document_types'];
        if ([] === $documentTypes) {
            $documentTypes = $this->documentTypeRepository->findActiveOrdered();
        }

        $builder
            ->add('documentType', EntityType::class, [
                'label' => 'Type de document',
                'class' => SiteDocumentType::class,
                'choices' => $documentTypes,
                'choice_label' => 'label',
                'placeholder' => 'Choisir un type',
                'required' => true,
            ])
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('file', FileType::class, [
                'label' => 'Document',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotNull(message: 'Veuillez sélectionner un fichier.'),
                    new File(
                        maxSize: '10M',
                        maxSizeMessage: 'Le document ne doit pas dépasser 10 Mo.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VenueDocument::class,
            'document_types' => [],
        ]);

        $resolver->setAllowedTypes('document_types', 'array');
    }
}
