<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $roles = $this->roleRepository
            ->createQueryBuilder('r')
            ->andWhere('r.isActive = true')
            ->getQuery()
            ->getResult();

        usort($roles, function (Role $left, Role $right): int {
            $leftLabel = $this->resolveRoleLabel($left);
            $rightLabel = $this->resolveRoleLabel($right);

            return strcasecmp($leftLabel, $rightLabel);
        });

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'user.firstname.required'
                    ),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'user.lastname.required'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'user.email.required'
                    ),
                    new Assert\Sequentially([
                        new Assert\Email(
                            message: 'user.email.invalid'
                        ),
                        new Assert\Regex(
                            pattern: '/^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$/',
                            message: 'user.email.invalid'
                        ),
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => (bool) $options['require_password'],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'user.password.required',
                        groups: ['password']
                    ),
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
            ->add('roleEntities', EntityType::class, [
                'label' => 'Rôles',
                'class' => Role::class,
                'choices' => $roles,
                'choice_label' => fn (Role $role) => $this->resolveRoleLabel($role),
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'required' => true,
                'constraints' => [
                    new Assert\Count(
                        min: 1,
                        minMessage: 'user.roles.required'
                    ),
                ],
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

    private function resolveRoleLabel(Role $role): string
    {
        $code = (string) $role->getCode();
        $translated = $this->translator->trans('roles.'.$code, [], 'messages');

        if ($translated === 'roles.'.$code) {
            $fallback = $role->getLabel();

            return null !== $fallback && '' !== $fallback ? $fallback : $code;
        }

        return $translated;
    }
}
