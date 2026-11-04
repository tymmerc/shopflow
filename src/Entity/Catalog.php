<?php

namespace App\Entity;

use App\Repository\CatalogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogRepository::class)]
class Catalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: '`desc`', type: Types::TEXT, nullable: true)]
    private ?string $desc = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $category = null;

    /** Débutant, Intermédiaire ou Confirmé */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $level = null;

    /** « À quoi ça sert », en une phrase, avant la technique */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(nullable: true)]
    private ?bool $solderingRequired = null;

    /** @var list<array{0: string, 1: string}>|null caractéristiques techniques : paires [libellé, valeur avec unité], dans l'ordre d'affichage */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $specs = null;

    /** @var string[]|null cartes avec lesquelles le produit fonctionne */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $compatibility = null;

    /** @var string[]|null ce qui est livré dans la boîte */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $boxContents = null;

    /** @var string[]|null noms des produits à prévoir avec celui-ci (choisis à la main) */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $accessories = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDesc(): ?string
    {
        return $this->desc;
    }

    public function setDesc(?string $desc): static
    {
        $this->desc = $desc;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTagline(): ?string
    {
        return $this->tagline;
    }

    public function setTagline(?string $tagline): static
    {
        $this->tagline = $tagline;

        return $this;
    }

    public function isSolderingRequired(): ?bool
    {
        return $this->solderingRequired;
    }

    public function setSolderingRequired(?bool $solderingRequired): static
    {
        $this->solderingRequired = $solderingRequired;

        return $this;
    }

    public function getSpecs(): ?array
    {
        return $this->specs;
    }

    public function setSpecs(?array $specs): static
    {
        $this->specs = $specs;

        return $this;
    }

    public function getCompatibility(): ?array
    {
        return $this->compatibility;
    }

    public function setCompatibility(?array $compatibility): static
    {
        $this->compatibility = $compatibility;

        return $this;
    }

    public function getBoxContents(): ?array
    {
        return $this->boxContents;
    }

    public function setBoxContents(?array $boxContents): static
    {
        $this->boxContents = $boxContents;

        return $this;
    }

    public function getAccessories(): ?array
    {
        return $this->accessories;
    }

    public function setAccessories(?array $accessories): static
    {
        $this->accessories = $accessories;

        return $this;
    }
}
