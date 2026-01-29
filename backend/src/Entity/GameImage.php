<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GameImageRepository;

#[ORM\Entity(repositoryClass: GameImageRepository::class)]
#[ORM\Table(name: 'game_images')]
class GameImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'bgg_id', nullable: false, onDelete: 'CASCADE')]
    private ?Game $game = null;

    #[ORM\Column(name: 'image_url', type: 'string', length: 2048)]
    private string $imageUrl;

    #[ORM\Column(name: 'is_primary', type: 'boolean')]
    private bool $isPrimary = false;

    public function __construct(string $imageUrl, bool $isPrimary = false)
    {
        $this->imageUrl = $imageUrl;
        $this->isPrimary = $isPrimary;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): self
    {
        $this->isPrimary = $isPrimary;
        return $this;
    }
}
