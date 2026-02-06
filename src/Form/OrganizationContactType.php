<?php

namespace App\Form;

use App\Entity\OrganizationContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OrganizationContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'placeholder' => 'Sélectionner un rôle',
                'choices' => [
                    'Demandeur' => 'REQUESTER',
                    'Payeur' => 'PAYER',
                    'Contact' => 'CONTACT',
                    'Autre' => 'OTHER',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'organization_contact.role.required'),
                ],
            ])
            ->add('title', ChoiceType::class, [
                'label' => 'Civilité',
                'required' => false,
                'placeholder' => 'Sélectionner',
                'choices' => [
                    'Mme' => 'Mme',
                    'M.' => 'M.',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'Fonction',
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(message: 'organization_contact.first_name.required'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(message: 'organization_contact.last_name.required'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(message: 'organization_contact.email.required'),
                    new Assert\Sequentially([
                        new Assert\Email(message: 'organization_contact.email.invalid'),
                        new Assert\Regex(
                            pattern: '/^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$/',
                            message: 'organization_contact.email.invalid'
                        ),
                    ]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrganizationContact::class,
        ]);
    }
}
