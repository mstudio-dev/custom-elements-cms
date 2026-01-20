<?php

namespace App\Entity;

use App\Repository\FormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormRepository::class)]
class Form
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $recipient = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $successMessage = null;

    #[ORM\Column]
    private bool $storeSubmissions = false;

    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormField::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sorting' => 'ASC'])]
    private Collection $fields;

    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormSubmission::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $submissions;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->successMessage = 'Vielen Dank! Ihre Nachricht wurde erfolgreich gesendet.';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(?string $successMessage): static
    {
        $this->successMessage = $successMessage;
        return $this;
    }

    public function isStoreSubmissions(): bool
    {
        return $this->storeSubmissions;
    }

    public function setStoreSubmissions(bool $storeSubmissions): static
    {
        $this->storeSubmissions = $storeSubmissions;
        return $this;
    }

    /**
     * @return Collection<int, FormField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(FormField $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setForm($this);
        }
        return $this;
    }

    public function removeField(FormField $field): static
    {
        if ($this->fields->removeElement($field)) {
            if ($field->getForm() === $this) {
                $field->setForm(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, FormSubmission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Formular #' . $this->id;
    }
}
