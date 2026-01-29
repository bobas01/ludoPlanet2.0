<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderItemRepository;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'bgg_id', nullable: false, onDelete: 'RESTRICT')]
    private ?Game $game = null;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(name: 'unit_price_cents', type: 'integer')]
    private int $unitPriceCents = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Order $order, Game $game, int $quantity = 1, int $unitPriceCents = 0)
    {
        $this->order = $order;
        $this->game = $game;
        $this->quantity = $quantity;
        $this->unitPriceCents = $unitPriceCents;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function setUnitPriceCents(int $unitPriceCents): self
    {
        $this->unitPriceCents = $unitPriceCents;
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
}
