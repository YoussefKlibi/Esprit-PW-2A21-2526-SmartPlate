<?php
class Profil {
    private ?int $id;
    private string $titre;
    private string $description;
    private int $id_utilisateur;

    public function __construct(string $titre, string $description, int $id_utilisateur, ?int $id = null) {
        $this->titre = $titre;
        $this->description = $description;
        $this->id_utilisateur = $id_utilisateur;
        $this->id = $id;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getIdUtilisateur(): int {
        return $this->id_utilisateur;
    }

    public function getId(): ?int {
        return $this->id;
    }
}
?>
