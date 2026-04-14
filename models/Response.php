<?php

class Response
{
    private ?int $id;
    private ?int $id_reclamation;
    private ?string $reponse;
    private ?string $date_reponse;

    public function __construct(
        ?int $id = null,
        ?int $id_reclamation = null,
        ?string $reponse = null,
        ?string $date_reponse = null
    ) {
        $this->id = $id;
        $this->id_reclamation = $id_reclamation;
        $this->reponse = $reponse;
        $this->date_reponse = $date_reponse;
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

    public function getIdReclamation(): ?int
    {
        return $this->id_reclamation;
    }

    public function setIdReclamation(?int $id_reclamation): self
    {
        $this->id_reclamation = $id_reclamation;
        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): self
    {
        $this->reponse = $reponse;
        return $this;
    }

    public function getDateReponse(): ?string
    {
        return $this->date_reponse;
    }

    public function setDateReponse(?string $date_reponse): self
    {
        $this->date_reponse = $date_reponse;
        return $this;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) ? (int) $data['id'] : null,
            isset($data['id_reclamation']) ? (int) $data['id_reclamation'] : null,
            $data['reponse'] ?? null,
            $data['date_reponse'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'id_reclamation' => $this->id_reclamation,
            'reponse' => $this->reponse,
            'date_reponse' => $this->date_reponse,
        ];
    }
}
