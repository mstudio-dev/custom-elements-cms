<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $imageIds = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $author = null;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column(length: 20)]
    private string $status = 'draft';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImageIds(): ?array
    {
        return $this->imageIds;
    }

    public function setImageIds(?array $imageIds): static
    {
        $this->imageIds = $imageIds;

        return $this;
    }

    public function getFirstImageId(): ?int
    {
        return $this->imageIds[0] ?? null;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->publishedAt !== null && $this->publishedAt <= new \DateTime();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTeaser(int $length = 300): string
    {
        $text = strip_tags($this->content);
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . '...';
    }
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): static
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    public function getEffectiveMetaTitle(): string
    {
        return $this->metaTitle ?? $this->title ?? '';
    }

    public function getEffectiveMetaDescription(): string
    {
        return $this->metaDescription ?? $this->getTeaser(160) ?? '';
    }
    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
