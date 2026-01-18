<?php

namespace App\Entity;

use App\Repository\ElementTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElementTypeRepository::class)]
class ElementType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: 'json')]
    private array $fields = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $template = null;

    #[ORM\OneToMany(mappedBy: 'elementType', targetEntity: Element::class, orphanRemoval: true)]
    private Collection $elements;

    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        return $this;
    }

    public function getElements(): Collection
    {
        return $this->elements;
    }

    // Virtuelle Properties für EasyAdmin
    public function getFieldsJson(): ?string
    {
        return json_encode($this->fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function setFieldsJson(?string $fieldsJson): self
    {
        $this->fields = $fieldsJson ? json_decode($fieldsJson, true) : [];
        return $this;
    }

    public function __toString(): string
    {
        return $this->label ?? $this->name ?? 'ElementType #' . $this->id;
    }
}