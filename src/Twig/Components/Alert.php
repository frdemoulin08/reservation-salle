<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/alert.html.twig')]
class Alert
{
    public string $type = 'info';
    public ?string $title = null;
    public ?string $message = null;
    public ?string $actionLabel = null;
    public ?string $actionHref = null;

    private const TYPE_STYLES = [
        'info' => ['text' => 'text-fg-brand-strong', 'bg' => 'bg-brand-softer'],
        'warning' => ['text' => 'text-fg-warning', 'bg' => 'bg-warning-soft'],
        'success' => ['text' => 'text-fg-success-strong', 'bg' => 'bg-success-soft'],
        'danger' => ['text' => 'text-fg-danger-strong', 'bg' => 'bg-danger-soft'],
        'neutral' => ['text' => 'text-heading', 'bg' => 'bg-neutral-secondary-medium'],
    ];

    public function getWrapperClass(): string
    {
        $styles = self::TYPE_STYLES[$this->type] ?? self::TYPE_STYLES['info'];

        return sprintf('rounded-base %s %s', $styles['text'], $styles['bg']);
    }
}
