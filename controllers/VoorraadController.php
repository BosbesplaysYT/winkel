<?php

class VoorraadController extends Controller
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index(): void
    {
        $this->vereisRol('beheerder');

        $voorraad = $this->productModel->getVoorraadPerGroep();

        $this->toonView('voorraad', [
            'gebruikersnaam' => $_SESSION['gebruikersnaam'],
            'rol'            => $_SESSION['rol'],
            'voorraad'       => $voorraad,
            'melding'        => $_SESSION['melding'] ?? null,
            'fout'           => $_SESSION['fout'] ?? null,
        ]);

        unset($_SESSION['melding'], $_SESSION['fout']);
    }

    /**
     * Verwerkt handmatige aanpassingen van een product
     */
    public function bewerk(): void
    {
        $this->vereisRol('beheerder');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $artikelnummer = $_POST['artikelnummer'] ?? '';
            $naam          = trim($_POST['artikelnaam'] ?? '');
            $groepNaam     = trim($_POST['artikelgroep'] ?? '');
            $prijs         = (float) str_replace(',', '.', $_POST['prijs'] ?? 0);
            $santal        = (int) ($_POST['santal'] ?? 0);

            if (!empty($artikelnummer) && !empty($naam)) {
                // We hergebruiken de logica van importeerProduct maar dwingen 
                // een overschrijving van de voorraad af in plaats van een optelling (+).
                // Hiervoor voegen we idealiter een update methode toe aan het model:
                $succes = $this->productModel->updateProductHandmatig(
                    $artikelnummer,
                    $naam,
                    $groepNaam,
                    $prijs,
                    $santal
                );

                if ($succes) {
                    $_SESSION['melding'] = "Product $artikelnummer succesvol bijgewerkt.";
                } else {
                    $_SESSION['fout'] = "Er is iets fout gegaan bij het bijwerken.";
                }
            }
        }

        $this->doorsturen('index.php?controller=voorraad&action=index');
    }

    public function importeer(): void
    {
        $this->vereisRol('beheerder');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_bestand'])) {
            $this->doorsturen('index.php?controller=voorraad&action=index');
        }

        $bestand = $_FILES['csv_bestand'];

        if ($bestand['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['fout'] = 'Fout bij het uploaden van het bestand.';
            $this->doorsturen('index.php?controller=voorraad&action=index');
        }

        $ext = strtolower(pathinfo($bestand['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $_SESSION['fout'] = 'Alleen CSV-bestanden zijn toegestaan.';
            $this->doorsturen('index.php?controller=voorraad&action=index');
        }

        $resultaat = $this->verwerkCsv($bestand['tmp_name']);

        if ($resultaat['succes']) {
            $_SESSION['melding'] = "Import geslaagd: {$resultaat['toegevoegd']} product(en) toegevoegd, {$resultaat['bijgewerkt']} bijgewerkt.";
        } else {
            $_SESSION['fout'] = 'Import mislukt: ' . $resultaat['fout'];
        }

        $this->doorsturen('index.php?controller=voorraad&action=index');
    }

    private function verwerkCsv(string $pad): array
    {
        $handle = fopen($pad, 'r');
        if ($handle === false) {
            return ['succes' => false, 'fout' => 'Bestand kon niet worden geopend.'];
        }

        $header = fgetcsv($handle, 1000, ';');
        if (!$header) {
            fclose($handle);
            return ['succes' => false, 'fout' => 'CSV-bestand is leeg of heeft geen header.'];
        }

        $toegevoegd = 0;
        $bijgewerkt = 0;

        while (($rij = fgetcsv($handle, 1000, ';')) !== false) {
            if (count($rij) < 5) continue;

            [$artikelnummer, $artikelnaam, $artikelgroep, $prijs, $santal] = $rij;

            $uitkomst = $this->productModel->importeerProduct(
                trim($artikelnummer),
                trim($artikelnaam),
                trim($artikelgroep),
                (float) str_replace(',', '.', trim($prijs)),
                (int) trim($santal)
            );

            if ($uitkomst === 'nieuw')      $toegevoegd++;
            elseif ($uitkomst === 'bijgewerkt') $bijgewerkt++;
        }

        fclose($handle);
        return ['succes' => true, 'toegevoegd' => $toegevoegd, 'bijgewerkt' => $bijgewerkt];
    }
}
