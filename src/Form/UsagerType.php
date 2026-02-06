<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UsagerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(message: 'user.firstname.required'),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(message: 'user.lastname.required'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(message: 'user.email.required'),
                    new Assert\Sequentially([
                        new Assert\Email(message: 'user.email.invalid'),
                        new Assert\Regex(
                            pattern: '/^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$/',
                            message: 'user.email.invalid'
                        ),
                    ]),
                ],
            ])
            ->add('organization', EntityType::class, [
                'label' => 'Organisation',
                'class' => Organization::class,
                'choice_label' => static function (Organization $organization): string {
                    $displayName = $organization->getDisplayName();
                    if ('' !== $displayName) {
                        return $displayName;
                    }

                    return $organization->getLegalName();
                },
                'query_builder' => static function ($repository) {
                    return $repository
                        ->createQueryBuilder('o')
                        ->addOrderBy('o.displayName', 'ASC')
                        ->addOrderBy('o.legalName', 'ASC');
                },
                'placeholder' => 'Aucune',
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => (bool) $options['require_password'],
                'constraints' => [
                    new Assert\NotBlank(message: 'user.password.required', groups: ['password']),
                    new Assert\Length(
                        min: 12,
                        max: 64,
                        minMessage: 'user.password.min_length',
                        maxMessage: 'user.password.max_length',
                        groups: ['password']
                    ),
                    new Assert\Regex(
                        pattern: '/^[A-Za-z0-9!\"#$%&\'()*+,\\-\\.\\/:;<=>\\?@\\[\\]\\\\^_{|}~`€£¥§¤]+$/u',
                        message: 'user.password.invalid_chars',
                        groups: ['password']
                    ),
                    new Assert\Regex(
                        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!\"#$%&\'()*+,\\-\\.\\/:;<=>\\?@\\[\\]\\\\^_{|}~`€£¥§¤]).+$/u',
                        message: 'user.password.categories',
                        groups: ['password']
                    ),
                ],
            ])
            ->add('mobilePhone', TelType::class, [
                'label' => 'Téléphone mobile',
                'required' => false,
            ])
            ->add('fixedPhone', TelType::class, [
                'label' => 'Téléphone fixe',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true,
        ]);
    }
}
