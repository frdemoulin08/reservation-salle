<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RoomPricing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'roomPricings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(length: 50)]
    private string $priceCategory = '';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $hourlyRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dailyRate = null;

    #[ORM\Column(length: 3, options: ['default' => 'EUR'])]
    private string $currency = 'EUR';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getPriceCategory(): string
    {
        return $this->priceCategory;
    }

    public function setPriceCategory(string $priceCategory): self
    {
        $this->priceCategory = $priceCategory;

        return $this;
    }

    public function getHourlyRate(): ?string
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?string $hourlyRate): self
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function getDailyRate(): ?string
    {
        return $this->dailyRate;
    }

    public function setDailyRate(?string $dailyRate): self
    {
        $this->dailyRate = $dailyRate;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
