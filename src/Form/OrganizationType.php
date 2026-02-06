<?php

namespace App\Form;

use App\Entity\Embeddable\Address;
use App\Entity\Organization;
use App\Repository\CountryRepository;
use App\Reference\OrganizationLegalNature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Luhn;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrganizationType extends AbstractType
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
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
            ->add('siret', TextType::class, [
                'label' => 'SIRET',
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'inputmode' => 'numeric',
                    'autocomplete' => 'off',
                    'pattern' => '\\d{14}',
                ],
                'constraints' => [
                    new Length(min: 14, max: 14, exactMessage: 'organization.siret.length'),
                    new Luhn(message: 'organization.siret.invalid'),
                ],
            ])
            ->add('legalName', TextType::class, [
                'label' => 'Dénomination légale',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'organization.legal_name.required'),
                ],
            ])
            ->add('displayName', TextType::class, [
                'label' => 'Nom d\'usage',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'organization.display_name.required'),
                ],
            ])
            ->add('organizationType', ChoiceType::class, [
                'label' => 'Type de structure',
                'required' => true,
                'placeholder' => 'Sélectionner un type',
                'choices' => [
                    'Association' => 'ASSOCIATION',
                    'Entreprise' => 'ENTREPRISE',
                    'Collectivité' => 'COLLECTIVITE',
                    'Conseil départemental des Ardennes' => 'CD08_SERVICE',
                    'Autre' => 'AUTRE',
                ],
                'constraints' => [
                    new NotBlank(message: 'organization.organization_type.required'),
                ],
            ])
            ->add('associationRegistered', CheckboxType::class, [
                'label' => 'Association immatriculée',
                'required' => false,
            ])
            ->add('legalNature', ChoiceType::class, [
                'label' => 'Nature juridique',
                'required' => false,
                'placeholder' => 'Sélectionner une nature juridique',
                'choices' => OrganizationLegalNature::choices(),
                'choice_attr' => static fn (mixed $choice, string $label, string $value): array => [
                    'data-legal-types' => implode(',', OrganizationLegalNature::typesFor($value)),
                ],
            ])
            ->add('billingSameAsHeadOffice', CheckboxType::class, [
                'label' => 'Adresse de facturation identique au siège',
                'required' => false,
            ])
            ->add('headOfficeAddressCountry', ChoiceType::class, [
                'label' => 'Pays',
                'required' => true,
                'property_path' => 'headOfficeAddress.country',
                'choices' => $countryChoices,
                'placeholder' => 'Choisir un pays',
                'constraints' => [
                    new NotBlank(message: 'organization.head_office.country.required'),
                ],
            ])
            ->add('headOfficeAddressLine1', TextType::class, [
                'label' => 'Adresse (siège)',
                'required' => true,
                'property_path' => 'headOfficeAddress.line1',
                'constraints' => [
                    new NotBlank(message: 'organization.head_office.address.required'),
                ],
            ])
            ->add('headOfficeAddressLine2', TextType::class, [
                'label' => 'Complément d\'adresse (siège)',
                'required' => false,
                'property_path' => 'headOfficeAddress.line2',
            ])
            ->add('headOfficeAddressLine3', TextType::class, [
                'label' => 'Complément d\'adresse (suite)',
                'required' => false,
                'property_path' => 'headOfficeAddress.line3',
            ])
            ->add('headOfficeAddressPostalCode', TextType::class, [
                'label' => 'Code postal (siège)',
                'required' => true,
                'property_path' => 'headOfficeAddress.postalCode',
                'constraints' => [
                    new NotBlank(message: 'organization.head_office.postal_code.required'),
                ],
            ])
            ->add('headOfficeAddressCity', TextType::class, [
                'label' => 'Commune (siège)',
                'required' => true,
                'empty_data' => '',
                'property_path' => 'headOfficeAddress.city',
                'constraints' => [
                    new NotBlank(message: 'organization.head_office.city.required'),
                ],
            ])
            ->add('billingAddressCountry', ChoiceType::class, [
                'label' => 'Pays (facturation)',
                'required' => true,
                'property_path' => 'billingAddress.country',
                'choices' => $countryChoices,
                'placeholder' => 'Choisir un pays',
                'constraints' => [
                    new NotBlank(message: 'organization.billing.country.required'),
                ],
            ])
            ->add('billingAddressLine1', TextType::class, [
                'label' => 'Adresse (facturation)',
                'required' => true,
                'property_path' => 'billingAddress.line1',
                'constraints' => [
                    new NotBlank(message: 'organization.billing.address.required'),
                ],
            ])
            ->add('billingAddressLine2', TextType::class, [
                'label' => 'Complément d\'adresse (facturation)',
                'required' => false,
                'property_path' => 'billingAddress.line2',
            ])
            ->add('billingAddressLine3', TextType::class, [
                'label' => 'Complément d\'adresse (suite)',
                'required' => false,
                'property_path' => 'billingAddress.line3',
            ])
            ->add('billingAddressPostalCode', TextType::class, [
                'label' => 'Code postal (facturation)',
                'required' => true,
                'property_path' => 'billingAddress.postalCode',
                'constraints' => [
                    new NotBlank(message: 'organization.billing.postal_code.required'),
                ],
            ])
            ->add('billingAddressCity', TextType::class, [
                'label' => 'Commune (facturation)',
                'required' => true,
                'empty_data' => '',
                'property_path' => 'billingAddress.city',
                'constraints' => [
                    new NotBlank(message: 'organization.billing.city.required'),
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $organization = $event->getData();
            if (!$organization instanceof Organization) {
                return;
            }

            if (null === $organization->getHeadOfficeAddress()) {
                $organization->setHeadOfficeAddress(new Address());
            }

            if (null === $organization->getBillingAddress()) {
                $organization->setBillingAddress(new Address());
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            $sameAsHeadOffice = filter_var($data['billingSameAsHeadOffice'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (!$sameAsHeadOffice) {
                return;
            }

            $map = [
                'Country' => 'Country',
                'Line1' => 'Line1',
                'Line2' => 'Line2',
                'Line3' => 'Line3',
                'PostalCode' => 'PostalCode',
                'City' => 'City',
            ];

            foreach ($map as $suffix => $targetSuffix) {
                $headKey = 'headOfficeAddress'.$suffix;
                $billingKey = 'billingAddress'.$targetSuffix;
                if (array_key_exists($headKey, $data)) {
                    $data[$billingKey] = $data[$headKey];
                }
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organization::class,
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
