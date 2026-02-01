<?php

namespace App\Form;

use App\Entity\Venue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du site',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'venue.name.required'),
                ],
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'property_path' => 'address.line1',
            ])
            ->add('addressPostalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'property_path' => 'address.postalCode',
            ])
            ->add('addressCity', TextType::class, [
                'label' => 'Commune',
                'empty_data' => '',
                'property_path' => 'address.city',
                'constraints' => [
                    new NotBlank(message: 'venue.city.required'),
                ],
            ])
            ->add('addressCountry', TextType::class, [
                'label' => 'Pays',
                'required' => false,
                'property_path' => 'address.country',
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
