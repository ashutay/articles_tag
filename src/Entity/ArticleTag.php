<?php

namespace App\Entity;

use App\Repository\ArticleTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleTagRepository::class)]
class ArticleTag
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'articleTags')]
    #[ORM\JoinColumn(nullable: false)]
    private Article $article;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'articleTags')]
    #[ORM\JoinColumn(nullable: false)]
    private Tag $tag;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
