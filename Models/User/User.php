<?php
class User {
    private ?int $id;
    private string $prenom;
    private string $nom;
    private string $email;
    private string $mot_de_passe;
    private ?string $google_id;
    private ?string $reset_token;
    private ?string $token_expires;
    private ?string $created_at;

    public function __construct(
        string $prenom, 
        string $nom, 
        string $email, 
        string $mot_de_passe, 
        ?int $id = null,
        ?string $google_id = null,
        ?string $reset_token = null,
        ?string $token_expires = null,
        ?string $created_at = null
    ) {
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->id = $id;
        $this->google_id = $google_id;
        $this->reset_token = $reset_token;
        $this->token_expires = $token_expires;
        $this->created_at = $created_at;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): string { return $this->email; }
    public function getMotDePasse(): string { return $this->mot_de_passe; }
    public function getGoogleId(): ?string { return $this->google_id; }
    public function getResetToken(): ?string { return $this->reset_token; }
    public function getTokenExpires(): ?string { return $this->token_expires; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    
    // Setters
    public function setMotDePasse(string $mot_de_passe): void { $this->mot_de_passe = $mot_de_passe; }
    public function setResetToken(?string $token): void { $this->reset_token = $token; }
    public function setTokenExpires(?string $expires): void { $this->token_expires = $expires; }
}
?>