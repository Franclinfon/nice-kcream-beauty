<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 180)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $conseilsUtilisation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixPromo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateDebutPromo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateFinPromo = null;

    #[ORM\Column(nullable: true)]
    private ?int $stockQuantity = null;

    #[ORM\Column]
    private ?bool $isRupture = null;

    #[ORM\Column]
    private ?bool $isNouveaute = null;

    #[ORM\Column]
    private ?bool $isCoffret = null;

    #[ORM\Column]
    private ?bool $isMiseEnAvant = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productImages;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(name: 'product_related_products')]
    private Collection $relatedProducts;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(
        name: 'product_coffret_items',
        joinColumns: [new ORM\JoinColumn(name: 'coffret_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id')]
    )]
    private Collection $coffretItems;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $orderItems;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isRupture = false;
        $this->isNouveaute = false;
        $this->isCoffret = false;
        $this->isMiseEnAvant = false;
        $this->isActive = true;
        $this->productImages = new ArrayCollection();
        $this->relatedProducts = new ArrayCollection();
        $this->coffretItems = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getConseilsUtilisation(): ?string
    {
        return $this->conseilsUtilisation;
    }

    public function setConseilsUtilisation(?string $conseilsUtilisation): static
    {
        $this->conseilsUtilisation = $conseilsUtilisation;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPrixPromo(): ?string
    {
        return $this->prixPromo;
    }

    public function setPrixPromo(?string $prixPromo): static
    {
        $this->prixPromo = $prixPromo;

        return $this;
    }

    public function getDateDebutPromo(): ?\DateTimeImmutable
    {
        return $this->dateDebutPromo;
    }

    public function setDateDebutPromo(?\DateTimeImmutable $dateDebutPromo): static
    {
        $this->dateDebutPromo = $dateDebutPromo;

        return $this;
    }

    public function getDateFinPromo(): ?\DateTimeImmutable
    {
        return $this->dateFinPromo;
    }

    public function setDateFinPromo(?\DateTimeImmutable $dateFinPromo): static
    {
        $this->dateFinPromo = $dateFinPromo;

        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(?int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function isRupture(): ?bool
    {
        return $this->isRupture;
    }

    public function setIsRupture(bool $isRupture): static
    {
        $this->isRupture = $isRupture;

        return $this;
    }

    public function isNouveaute(): ?bool
    {
        return $this->isNouveaute;
    }

    public function setIsNouveaute(bool $isNouveaute): static
    {
        $this->isNouveaute = $isNouveaute;

        return $this;
    }

    public function isCoffret(): ?bool
    {
        return $this->isCoffret;
    }

    public function setIsCoffret(bool $isCoffret): static
    {
        $this->isCoffret = $isCoffret;

        return $this;
    }

    public function isMiseEnAvant(): ?bool
    {
        return $this->isMiseEnAvant;
    }

    public function setIsMiseEnAvant(bool $isMiseEnAvant): static
    {
        $this->isMiseEnAvant = $isMiseEnAvant;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getRelatedProducts(): Collection
    {
        return $this->relatedProducts;
    }

    public function addRelatedProduct(Product $product): static
    {
        if (!$this->relatedProducts->contains($product)) {
            $this->relatedProducts->add($product);
        }

        return $this;
    }

    public function removeRelatedProduct(Product $product): static
    {
        $this->relatedProducts->removeElement($product);

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getCoffretItems(): Collection
    {
        return $this->coffretItems;
    }

    public function addCoffretItem(Product $product): static
    {
        if (!$this->coffretItems->contains($product)) {
            $this->coffretItems->add($product);
        }

        return $this;
    }

    public function removeCoffretItem(Product $product): static
    {
        $this->coffretItems->removeElement($product);

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

        return $this;
    }
}