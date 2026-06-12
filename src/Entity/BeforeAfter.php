<?php

namespace App\Entity;

use App\Repository\BeforeAfterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BeforeAfterRepository::class)]
class BeforeAfter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $imageAvant = null;

    #[ORM\Column(length: 255)]
    private ?string $imageApres = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?int $position = null;

    public function __construct()
    {
        $this->isActive = true;
        $this->position = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getImageAvant(): ?string
    {
        return $this->imageAvant;
    }

    public function setImageAvant(string $imageAvant): static
    {
        $this->imageAvant = $imageAvant;

        return $this;
    }

    public function getImageApres(): ?string
    {
        return $this->imageApres;
    }

    public function setImageApres(string $imageApres): static
    {
        $this->imageApres = $imageApres;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}