<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CartItemRepository;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\Table(name: 'cart_items')]
#[ORM\UniqueConstraint(name: 'uq_cart_game', columns: ['cart_id', 'game_id'])]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Cart $cart = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'bgg_id', nullable: false, onDelete: 'CASCADE')]
    private ?Game $game = null;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Cart $cart, Game $game, int $quantity = 1)
    {
        $this->cart = $cart;
        $this->game = $game;
        $this->quantity = $quantity;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;
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
}
