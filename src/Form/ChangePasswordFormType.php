<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', PasswordType::class, [
            'label' => 'Mot de passe',
            'mapped' => false,
            'constraints' => [
                new Assert\NotBlank(
                    message: 'user.password.required'
                ),
                new Assert\Length(
                    min: 12,
                    max: 64,
                    minMessage: 'user.password.min_length',
                    maxMessage: 'user.password.max_length'
                ),
                new Assert\Regex(
                    pattern: '/^[A-Za-z0-9!\"#$%&\'()*+,\\-\\.\\/:;<=>\\?@\\[\\]\\\\^_{|}~`€£¥§¤]+$/u',
                    message: 'user.password.invalid_chars'
                ),
                new Assert\Regex(
                    pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!\"#$%&\'()*+,\\-\\.\\/:;<=>\\?@\\[\\]\\\\^_{|}~`€£¥§¤]).+$/u',
                    message: 'user.password.categories'
                ),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}
