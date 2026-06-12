<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\Column(length: 50)]
    private ?string $numeroCommande = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montantTotal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(length: 30)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 100)]
    private ?string $livraisonNom = null;

    #[ORM\Column(length: 255)]
    private ?string $livraisonRue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $livraisonComplement = null;

    #[ORM\Column(length: 10)]
    private ?string $livraisonCodePostal = null;

    #[ORM\Column(length: 100)]
    private ?string $livraisonVille = null;

    #[ORM\Column(length: 100)]
    private ?string $livraisonPays = null;

    #[ORM\Column(length: 20)]
    private ?string $livraisonTelephone = null;

    #[ORM\Column(length: 180)]
    private ?string $livraisonEmail = null;

    #[ORM\Column(length: 30)]
    private ?string $deliveryMethod = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $shippingCost = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'commande', orphanRemoval: true)]
    private Collection $orderItems;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->statut = 'en_attente_paiement';
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    public function getMontantTotal(): ?string
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(string $montantTotal): static
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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

    public function getLivraisonNom(): ?string
    {
        return $this->livraisonNom;
    }

    public function setLivraisonNom(string $livraisonNom): static
    {
        $this->livraisonNom = $livraisonNom;

        return $this;
    }

    public function getLivraisonRue(): ?string
    {
        return $this->livraisonRue;
    }

    public function setLivraisonRue(string $livraisonRue): static
    {
        $this->livraisonRue = $livraisonRue;

        return $this;
    }

    public function getLivraisonComplement(): ?string
    {
        return $this->livraisonComplement;
    }

    public function setLivraisonComplement(?string $livraisonComplement): static
    {
        $this->livraisonComplement = $livraisonComplement;

        return $this;
    }

    public function getLivraisonCodePostal(): ?string
    {
        return $this->livraisonCodePostal;
    }

    public function setLivraisonCodePostal(string $livraisonCodePostal): static
    {
        $this->livraisonCodePostal = $livraisonCodePostal;

        return $this;
    }

    public function getLivraisonVille(): ?string
    {
        return $this->livraisonVille;
    }

    public function setLivraisonVille(string $livraisonVille): static
    {
        $this->livraisonVille = $livraisonVille;

        return $this;
    }

    public function getLivraisonPays(): ?string
    {
        return $this->livraisonPays;
    }

    public function setLivraisonPays(string $livraisonPays): static
    {
        $this->livraisonPays = $livraisonPays;

        return $this;
    }

    public function getLivraisonTelephone(): ?string
    {
        return $this->livraisonTelephone;
    }

    public function setLivraisonTelephone(string $livraisonTelephone): static
    {
        $this->livraisonTelephone = $livraisonTelephone;

        return $this;
    }

    public function getLivraisonEmail(): ?string
    {
        return $this->livraisonEmail;
    }

    public function setLivraisonEmail(string $livraisonEmail): static
    {
        $this->livraisonEmail = $livraisonEmail;

        return $this;
    }

    public function getDeliveryMethod(): ?string
    {
        return $this->deliveryMethod;
    }

    public function setDeliveryMethod(string $deliveryMethod): static
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    public function getShippingCost(): ?string
    {
        return $this->shippingCost;
    }

    public function setShippingCost(string $shippingCost): static
    {
        $this->shippingCost = $shippingCost;

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
            $orderItem->setCommande($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getCommande() === $this) {
                $orderItem->setCommande(null);
            }
        }

        return $this;
    }
}
