<?php
class DashboardController extends Controller
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index(): void
    {
        $this->vereisLogin();

        // Initialiseer mandje als het nog niet bestaat
        if (!isset($_SESSION['mandje'])) {
            $_SESSION['mandje'] = [];
        }

        $this->toonView('dashboard', [
            'gebruikersnaam' => $_SESSION['gebruikersnaam'],
            'rol'            => $_SESSION['rol'],
            'mandje'         => $_SESSION['mandje'],
            'totaal'         => $this->berekenTotaal(),
            'melding'        => $_SESSION['melding'] ?? null,
            'fout'           => $_SESSION['fout'] ?? null,
            'bon'            => $_SESSION['bon'] ?? null,
        ]);

        unset($_SESSION['melding'], $_SESSION['fout'], $_SESSION['bon']);
    }

    public function voegToe(): void
    {
        $this->vereisLogin();
        $barcode = trim($_POST['barcode'] ?? '');

        if ($barcode === '') {
            $this->doorsturen('index.php?controller=dashboard&action=index');
        }

        $product = $this->productModel->vindOpBarcode($barcode);

        if ($product) {
            $id = $product['artikelnummer'];
            $voorraad = (int)$product['santal']; // De voorraad uit de DB
            $huidigInMandje = $_SESSION['mandje'][$id]['aantal'] ?? 0;

            if ($voorraad <= $huidigInMandje) {
                $_SESSION['fout'] = 'Product "' . $product['artikelnaam'] . '" is niet meer op voorraad.';
            } else {
                if (isset($_SESSION['mandje'][$id])) {
                    $_SESSION['mandje'][$id]['aantal']++;
                } else {
                    $_SESSION['mandje'][$id] = [
                        'naam'   => $product['artikelnaam'],
                        'prijs'  => $product['prijs'],
                        'aantal' => 1,
                        'max_voorraad' => $voorraad // <-- Sla dit hier op!
                    ];
                }
            }
        } else {
            $_SESSION['fout'] = 'Onbekend artikelnummer: "' . htmlspecialchars($barcode) . '".';
        }

        $this->doorsturen('index.php?controller=dashboard&action=index');
    }

    public function verhoogAantal(): void
    {
        $this->vereisLogin();
        $id = $_POST['artikelnummer'] ?? '';

        if (isset($_SESSION['mandje'][$id])) {
            // We moeten hier OOK de voorraad checken!
            $product = $this->productModel->vindOpBarcode($id);

            if ($product && $product['santal'] > $_SESSION['mandje'][$id]['aantal']) {
                $_SESSION['mandje'][$id]['aantal']++;
            } else {
                $_SESSION['fout'] = 'Maximale voorraad bereikt voor dit product.';
            }
        }

        $this->doorsturen('index.php?controller=dashboard&action=index');
    }

    public function verwijderItem(): void
    {
        $this->vereisLogin();
        $id = $_POST['artikelnummer'] ?? '';

        if (isset($_SESSION['mandje'][$id])) {
            unset($_SESSION['mandje'][$id]);
        }

        $this->doorsturen('index.php?controller=dashboard&action=index');
    }

    public function verlaagAantal(): void
    {
        $this->vereisLogin();
        $id = $_POST['artikelnummer'] ?? '';

        if (isset($_SESSION['mandje'][$id])) {
            $_SESSION['mandje'][$id]['aantal']--;
            if ($_SESSION['mandje'][$id]['aantal'] <= 0) {
                unset($_SESSION['mandje'][$id]);
            }
        }

        $this->doorsturen('index.php?controller=dashboard&action=index');
    }
    public function leegMandje(): void
    {
        $_SESSION['mandje'] = [];
        $this->doorsturen('index.php?controller=dashboard&action=index');
    }

    private function berekenTotaal(): float
    {
        $totaal = 0;
        foreach ($_SESSION['mandje'] as $item) {
            $totaal += $item['prijs'] * $item['aantal'];
        }
        return $totaal;
    }

    public function afrekenen(): void
    {
        $this->vereisLogin();

        if (!empty($_SESSION['mandje'])) {
            $transactieModel = new TransactieModel();
            $totaal = $this->berekenTotaal();
            $btw = $totaal / 121 * 21;

            $succes = $transactieModel->slaTransactieOp(
                $_SESSION['gebruiker_id'],
                $totaal,
                $btw,
                $_SESSION['mandje']
            );

            if ($succes) {
                // Sla bongegevens op voor de modal – mandje daarna leegmaken
                $_SESSION['bon'] = [
                    'items'      => $_SESSION['mandje'],
                    'totaal'     => $totaal,
                    'btw'        => $btw,
                    'kassier'    => $_SESSION['gebruikersnaam'],
                    'datum_tijd' => date('d-m-Y H:i:s'),
                ];
                $_SESSION['mandje'] = [];
            } else {
                $_SESSION['fout'] = 'Afrekenen mislukt. Probeer het opnieuw of neem contact op met de beheerder.';
            }
        } else {
            $_SESSION['fout'] = 'De bon is leeg. Voeg eerst producten toe.';
        }

        $this->doorsturen('index.php?controller=dashboard&action=index');
    }

    public function zoeken(): void
    {
        $this->vereisLogin();
        $term = $_GET['zoekterm'] ?? '';
        $resultaten = [];

        if (strlen($term) >= 2) {
            $resultaten = $this->productModel->uitgebreidZoeken($term);
        }

        $this->toonView('dashboard', [
            'gebruikersnaam' => $_SESSION['gebruikersnaam'],
            'rol'            => $_SESSION['rol'],
            'mandje'         => $_SESSION['mandje'] ?? [],
            'totaal'         => $this->berekenTotaal(),
            'zoekResultaten' => $resultaten,
            'melding'        => $_SESSION['melding'] ?? null,
            'fout'           => $_SESSION['fout'] ?? null,
        ]);

        unset($_SESSION['melding'], $_SESSION['fout']);
    }
}
