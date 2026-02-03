<?php

namespace App\Form;

use App\Entity\Venue;
use App\Repository\CountryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenueType extends AbstractType
{
    public function __construct(private readonly CountryRepository $countryRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $countryChoices = [];
        foreach ($this->countryRepository->findActiveOrdered() as $country) {
            $countryChoices[$country->getLabel()] = $country->getCode();
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du site',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'venue.name.required'),
                ],
            ])
            ->add('addressCountry', ChoiceType::class, [
                'label' => 'Pays',
                'required' => true,
                'property_path' => 'address.country',
                'choices' => $countryChoices,
                'placeholder' => 'Choisir un pays',
                'constraints' => [
                    new NotBlank(message: 'venue.country.required'),
                ],
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Adresse',
                'required' => true,
                'property_path' => 'address.line1',
                'constraints' => [
                    new NotBlank(message: 'venue.address.required'),
                ],
            ])
            ->add('addressLine2', TextType::class, [
                'label' => 'Complément d’adresse',
                'required' => false,
                'property_path' => 'address.line2',
            ])
            ->add('addressLine3', TextType::class, [
                'label' => 'Complément d’adresse (suite)',
                'required' => false,
                'property_path' => 'address.line3',
            ])
            ->add('addressPostalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => true,
                'property_path' => 'address.postalCode',
                'constraints' => [
                    new NotBlank(message: 'venue.postal_code.required'),
                ],
            ])
            ->add('addressCity', TextType::class, [
                'label' => 'Commune',
                'empty_data' => '',
                'property_path' => 'address.city',
                'constraints' => [
                    new NotBlank(message: 'venue.city.required'),
                ],
            ])
            ->add('addressSource', HiddenType::class, [
                'required' => false,
                'property_path' => 'address.source',
            ])
            ->add('addressExternalId', HiddenType::class, [
                'required' => false,
                'property_path' => 'address.externalId',
            ])
            ->add('addressLatitude', HiddenType::class, [
                'required' => false,
                'property_path' => 'address.latitude',
            ])
            ->add('addressLongitude', HiddenType::class, [
                'required' => false,
                'property_path' => 'address.longitude',
            ])
            ->add('parkingType', ChoiceType::class, [
                'label' => 'Type de parking',
                'required' => false,
                'choices' => [
                    'Gratuit' => 'gratuit',
                    'Payant' => 'payant',
                ],
            ])
            ->add('parkingCapacity', IntegerType::class, [
                'label' => 'Nombre de places de parking',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
