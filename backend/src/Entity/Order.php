<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'pending';

    #[ORM\Column(name: 'total_cents', type: 'integer')]
    private int $totalCents = 0;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'EUR';

    #[ORM\Column(name: 'shipping_full_name', type: 'string', length: 255, nullable: true)]
    private ?string $shippingFullName = null;

    #[ORM\Column(name: 'shipping_address_line1', type: 'string', length: 255, nullable: true)]
    private ?string $shippingAddressLine1 = null;

    #[ORM\Column(name: 'shipping_address_line2', type: 'string', length: 255, nullable: true)]
    private ?string $shippingAddressLine2 = null;

    #[ORM\Column(name: 'shipping_postal_code', type: 'string', length: 20, nullable: true)]
    private ?string $shippingPostalCode = null;

    #[ORM\Column(name: 'shipping_city', type: 'string', length: 100, nullable: true)]
    private ?string $shippingCity = null;

    #[ORM\Column(name: 'shipping_country', type: 'string', length: 2, nullable: true)]
    private ?string $shippingCountry = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getTotalCents(): int
    {
        return $this->totalCents;
    }

    public function setTotalCents(int $totalCents): self
    {
        $this->totalCents = $totalCents;
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

    public function getShippingFullName(): ?string
    {
        return $this->shippingFullName;
    }

    public function setShippingFullName(?string $shippingFullName): self
    {
        $this->shippingFullName = $shippingFullName;
        return $this;
    }

    public function getShippingAddressLine1(): ?string
    {
        return $this->shippingAddressLine1;
    }

    public function setShippingAddressLine1(?string $shippingAddressLine1): self
    {
        $this->shippingAddressLine1 = $shippingAddressLine1;
        return $this;
    }

    public function getShippingAddressLine2(): ?string
    {
        return $this->shippingAddressLine2;
    }

    public function setShippingAddressLine2(?string $shippingAddressLine2): self
    {
        $this->shippingAddressLine2 = $shippingAddressLine2;
        return $this;
    }

    public function getShippingPostalCode(): ?string
    {
        return $this->shippingPostalCode;
    }

    public function setShippingPostalCode(?string $shippingPostalCode): self
    {
        $this->shippingPostalCode = $shippingPostalCode;
        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(?string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;
        return $this;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(?string $shippingCountry): self
    {
        $this->shippingCountry = $shippingCountry;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
        return $this;
    }

    public function removeItem(OrderItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }
        return $this;
    }
}
