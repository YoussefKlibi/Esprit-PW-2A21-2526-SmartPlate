<?php

class Reclamation
{
    private ?int $id;
    private ?string $nom_client;
    private ?string $email;
    private ?string $sujet;
    private ?string $message;
    private ?string $date_creation;

    public function __construct(
        ?int $id = null,
        ?string $nom_client = null,
        ?string $email = null,
        ?string $sujet = null,
        ?string $message = null,
        ?string $date_creation = null
    ) {
        $this->id = $id;
        $this->nom_client = $nom_client;
        $this->email = $email;
        $this->sujet = $sujet;
        $this->message = $message;
        $this->date_creation = $date_creation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nom_client;
    }

    public function setNomClient(?string $nom_client): self
    {
        $this->nom_client = $nom_client;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(?string $sujet): self
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(?string $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) ? (int) $data['id'] : null,
            $data['nom_client'] ?? null,
            $data['email'] ?? null,
            $data['sujet'] ?? null,
            $data['message'] ?? null,
            $data['date_creation'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom_client' => $this->nom_client,
            'email' => $this->email,
            'sujet' => $this->sujet,
            'message' => $this->message,
            'date_creation' => $this->date_creation,
        ];
    }
}
