<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Mechanic;
use App\Entity\Domain;
use App\Entity\Category;
use App\Entity\GameImage;
use App\Repository\GameRepository;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: 'games')]
class Game
{
    #[ORM\Id]
    #[ORM\Column(name: 'bgg_id', type: 'integer')]
    private int $bggId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'year_published', type: 'integer', nullable: true)]
    private ?int $yearPublished = null;

    #[ORM\Column(name: 'min_players', type: 'integer', nullable: true)]
    private ?int $minPlayers = null;

    #[ORM\Column(name: 'max_players', type: 'integer', nullable: true)]
    private ?int $maxPlayers = null;

    #[ORM\Column(name: 'play_time', type: 'integer', nullable: true)]
    private ?int $playTime = null;

    #[ORM\Column(name: 'min_age', type: 'integer', nullable: true)]
    private ?int $minAge = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'price_cents', type: 'integer', nullable: true)]
    private ?int $priceCents = null;

    #[ORM\Column(name: 'users_rated', type: 'integer', nullable: true)]
    private ?int $usersRated = null;

    #[ORM\Column(name: 'rating_average', type: 'decimal', precision: 4, scale: 2, nullable: true)]
    private ?string $ratingAverage = null;

    #[ORM\Column(name: 'bgg_rank', type: 'integer', nullable: true)]
    private ?int $bggRank = null;

    #[ORM\Column(name: 'complexity_average', type: 'decimal', precision: 4, scale: 2, nullable: true)]
    private ?string $complexityAverage = null;

    #[ORM\Column(name: 'owned_users', type: 'integer', nullable: true)]
    private ?int $ownedUsers = null;

    #[ORM\ManyToMany(targetEntity: Mechanic::class, inversedBy: 'games')]
    #[ORM\JoinTable(name: 'game_mechanics')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'bgg_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'mechanic_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $mechanics;

    #[ORM\ManyToMany(targetEntity: Domain::class, inversedBy: 'games')]
    #[ORM\JoinTable(name: 'game_domains')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'bgg_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'domain_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $domains;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'games')]
    #[ORM\JoinTable(name: 'game_categories')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'bgg_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: GameImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    public function __construct(int $bggId, string $name)
    {
        $this->bggId = $bggId;
        $this->name = $name;
        $this->mechanics = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getBggId(): int
    {
        return $this->bggId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getYearPublished(): ?int
    {
        return $this->yearPublished;
    }

    public function setYearPublished(?int $yearPublished): self
    {
        $this->yearPublished = $yearPublished;
        return $this;
    }

    public function getMinPlayers(): ?int
    {
        return $this->minPlayers;
    }

    public function setMinPlayers(?int $minPlayers): self
    {
        $this->minPlayers = $minPlayers;
        return $this;
    }

    public function getMaxPlayers(): ?int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(?int $maxPlayers): self
    {
        $this->maxPlayers = $maxPlayers;
        return $this;
    }

    public function getPlayTime(): ?int
    {
        return $this->playTime;
    }

    public function setPlayTime(?int $playTime): self
    {
        $this->playTime = $playTime;
        return $this;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): self
    {
        $this->minAge = $minAge;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPriceCents(): ?int
    {
        return $this->priceCents;
    }

    public function setPriceCents(?int $priceCents): self
    {
        $this->priceCents = $priceCents;
        return $this;
    }

    public function getUsersRated(): ?int
    {
        return $this->usersRated;
    }

    public function setUsersRated(?int $usersRated): self
    {
        $this->usersRated = $usersRated;
        return $this;
    }

    public function getRatingAverage(): ?string
    {
        return $this->ratingAverage;
    }

    public function setRatingAverage(?string $ratingAverage): self
    {
        $this->ratingAverage = $ratingAverage;
        return $this;
    }

    public function getBggRank(): ?int
    {
        return $this->bggRank;
    }

    public function setBggRank(?int $bggRank): self
    {
        $this->bggRank = $bggRank;
        return $this;
    }

    public function getComplexityAverage(): ?string
    {
        return $this->complexityAverage;
    }

    public function setComplexityAverage(?string $complexityAverage): self
    {
        $this->complexityAverage = $complexityAverage;
        return $this;
    }

    public function getOwnedUsers(): ?int
    {
        return $this->ownedUsers;
    }

    public function setOwnedUsers(?int $ownedUsers): self
    {
        $this->ownedUsers = $ownedUsers;
        return $this;
    }

    public function getMechanics(): Collection
    {
        return $this->mechanics;
    }

    public function addMechanic(Mechanic $mechanic): self
    {
        if (!$this->mechanics->contains($mechanic)) {
            $this->mechanics->add($mechanic);
            $mechanic->addGame($this);
        }
        return $this;
    }

    public function removeMechanic(Mechanic $mechanic): self
    {
        if ($this->mechanics->removeElement($mechanic)) {
            $mechanic->removeGame($this);
        }
        return $this;
    }

    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domain $domain): self
    {
        if (!$this->domains->contains($domain)) {
            $this->domains->add($domain);
            $domain->addGame($this);
        }
        return $this;
    }

    public function removeDomain(Domain $domain): self
    {
        if ($this->domains->removeElement($domain)) {
            $domain->removeGame($this);
        }
        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addGame($this);
        }
        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeGame($this);
        }
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(GameImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setGame($this);
        }
        return $this;
    }

    public function removeImage(GameImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getGame() === $this) {
                $image->setGame(null);
            }
        }
        return $this;
    }
}
