<?php
class User {
    private ?int $id;
    private string $prenom;
    private string $nom;
    private string $email;
    private string $mot_de_passe;

    public function __construct(string $prenom, string $nom, string $email, string $mot_de_passe, ?int $id = null) {
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->id = $id;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getMotDePasse(): string {
        return $this->mot_de_passe;
    }

    public function getId(): ?int {
        return $this->id;
    }
}
?>