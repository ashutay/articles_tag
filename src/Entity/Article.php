<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $title;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\OneToMany(targetEntity: ArticleTag::class, mappedBy: 'article', cascade: ['persist', 'remove'])]
    private Collection $articleTags;


    public function __construct()
    {
        $this->articleTags = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getArticleTags(): Collection
    {
        return $this->articleTags;
    }
}
