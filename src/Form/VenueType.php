<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Venue;
use App\Repository\CountryRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenueType extends AbstractType
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $countryChoices = [];
        foreach ($this->countryRepository->findActiveOrdered() as $country) {
            $label = $country->getLabel();
            $flag = $this->flagFromCountryCode($country->getCode());
            $countryChoices['' !== $flag ? sprintf('%s%s%s', $flag, "\u{00A0}\u{00A0}", $label) : $label] = $country->getCode();
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du site',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'venue.name.required'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Texte descriptif',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'venue.description.required'),
                    new Length(
                        max: 500,
                        maxMessage: 'venue.description.max_length'
                    ),
                ],
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 500,
                ],
            ])
            ->add('referenceContactUser', EntityType::class, [
                'label' => 'Référent SPSL',
                'class' => User::class,
                'required' => false,
                'placeholder' => 'Sélectionner un référent',
                'choice_label' => static fn (User $user) => sprintf(
                    '%s %s (%s)',
                    $user->getFirstname(),
                    $user->getLastname(),
                    $user->getEmail()
                ),
                'query_builder' => function () {
                    return $this->userRepository
                        ->createQueryBuilder('u')
                        ->distinct()
                        ->innerJoin('u.roles', 'role')
                        ->andWhere('u.isActive = true')
                        ->andWhere('role.isActive = true')
                        ->andWhere('role.code IN (:roles)')
                        ->setParameter('roles', [User::ROLE_BUSINESS_ADMIN, User::ROLE_APP_MANAGER])
                        ->addOrderBy('u.lastname', 'ASC')
                        ->addOrderBy('u.firstname', 'ASC');
                },
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
            ->add('publicTransportAccess', TextareaType::class, [
                'label' => 'Proximité des transports en commun',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
            ])
            ->add('deliveryAccess', TextareaType::class, [
                'label' => 'Accès livraison',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
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

    private function flagFromCountryCode(?string $code): string
    {
        $normalized = strtoupper(trim((string) $code));
        if (!preg_match('/^[A-Z]{2}$/', $normalized)) {
            return '';
        }

        $first = 0x1F1E6 + (ord($normalized[0]) - 65);
        $second = 0x1F1E6 + (ord($normalized[1]) - 65);

        $firstChar = $this->chr($first);
        $secondChar = $this->chr($second);

        if ('' === $firstChar || '' === $secondChar) {
            return '';
        }

        return $firstChar.$secondChar;
    }

    private function chr(int $codepoint): string
    {
        if (function_exists('mb_chr')) {
            return mb_chr($codepoint, 'UTF-8');
        }

        if (class_exists(\IntlChar::class)) {
            return \IntlChar::chr($codepoint) ?: '';
        }

        return '';
    }
}
