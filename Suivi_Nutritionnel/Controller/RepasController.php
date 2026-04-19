<?php
if (!class_exists('Config')) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../Model/Repas_Class.php';
require_once __DIR__ . '/../Model/Journal_Class.php';

class RepasController {
    private function redirectToJournal($journalId = null) {
        $fallback = '../View/FrontOffice/Journal.php';
        $target = $_SERVER['HTTP_REFERER'] ?? $fallback;

        if ($journalId) {
            $separator = (strpos($target, '?') !== false) ? '&' : '?';
            if (strpos($target, 'journal_id=') === false) {
                $target .= $separator . 'journal_id=' . urlencode((string)$journalId);
            }
        }

        header('Location: ' . $target);
        exit;
    }

    public function add() {
        if (!isset($_POST['id_journal'])) {
            $this->redirectToJournal();
        }
        $id_journal = (int)$_POST['id_journal'];
        $journal = Journal::getById($id_journal);
        if (!$journal) {
            $this->redirectToJournal();
        }

        $type = $_POST['type_repas'] ?? '';
        $heure = $_POST['heure_repas'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $quantite = $_POST['quantite'] ?? null;
        $calories = $_POST['nbre_calories'] ?? null;
        $proteine = $_POST['proteine'] ?? null;
        $glucide = $_POST['glucide'] ?? null;
        $lipide = $_POST['lipide'] ?? null;

        $imageName = null;
        if (!empty($_FILES['repas_image']['name']) && is_uploaded_file($_FILES['repas_image']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../uploads/repas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['repas_image']['name'], PATHINFO_EXTENSION);
            $imageName = 'repas_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
            move_uploaded_file($_FILES['repas_image']['tmp_name'], $uploadDir . $imageName);
        }

        $repas = new Repas($id_journal, $type, $heure, $nom, $quantite, $imageName, $calories, $proteine, $glucide, $lipide);
        $repas->ajouter();

        $this->redirectToJournal($id_journal);
    }

    public function delete($id) {
        $existing = Repas::getById($id);
        Repas::supprimer($id);
        $this->redirectToJournal($existing['id_journal'] ?? null);
    }

    public function update($id) {
        $existing = Repas::getById($id);
        if (!$existing) {
            $this->redirectToJournal();
        }

        $image_repas = $existing['image_repas'] ?? null;
        if (!empty($_FILES['repas_image']['name']) && is_uploaded_file($_FILES['repas_image']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../uploads/repas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['repas_image']['name'], PATHINFO_EXTENSION);
            $image_repas = 'repas_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
            move_uploaded_file($_FILES['repas_image']['tmp_name'], $uploadDir . $image_repas);
        }

        $data = [
            'id_journal' => (int)($_POST['id_journal'] ?? $existing['id_journal']),
            'nom' => $_POST['nom'] ?? $existing['nom'],
            'type_repas' => $_POST['type_repas'] ?? $existing['type_repas'],
            'heure_repas' => $_POST['heure_repas'] ?? $existing['heure_repas'],
            'quantite' => $_POST['quantite'] ?? $existing['quantite'],
            'image_repas' => $image_repas,
            'nbre_calories' => $_POST['nbre_calories'] ?? $existing['nbre_calories'],
            'proteine' => $_POST['proteine'] ?? $existing['proteine'],
            'glucide' => $_POST['glucide'] ?? $existing['glucide'],
            'lipide' => $_POST['lipide'] ?? $existing['lipide'],
        ];
        Repas::update($id, $data);
        $this->redirectToJournal($data['id_journal']);
    }
}

if (isset($_GET['action'])) {
    $c = new RepasController();
    if ($_GET['action'] === 'add') {
        $c->add();
    } elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $c->delete((int)$_GET['id']);
    } elseif ($_GET['action'] === 'update' && isset($_GET['id'])) {
        $c->update((int)$_GET['id']);
    }
}
