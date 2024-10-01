<?php

namespace App\Entity;

use App\Repository\AdvertsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertsRepository::class)]
class Adverts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est requis.")]
    #[Groups(["user"])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Groups(["user"])]
    private ?string $description = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Le prix est requis.")]
    #[Groups(["user"])]
    private float $price;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: "Le code postal est requis.")]
    #[Assert\Regex("/^\d{5}$/", message: "Le code postal doit comporter 5 chiffres.")]
    #[Groups(["user"])]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ville est requise.")]
    #[Groups(["user"])]
    private ?string $city = null;

    #[ORM\ManyToOne(inversedBy: 'adverts')]
    #[Groups(["user"])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
