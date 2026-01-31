<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BoolExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('to_bool', [$this, 'toBool']),
        ];
    }

    public function toBool(mixed $value, bool $default = false): bool
    {
        if (true === $value) {
            return true;
        }
        if (false === $value) {
            return false;
        }
        if (null === $value) {
            return $default;
        }

        // numériques & strings numériques
        if (1 === $value || '1' === $value) {
            return true;
        }
        if (0 === $value || '0' === $value) {
            return false;
        }

        // strings "true/false" et variantes
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['true', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($v, ['false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }
}
