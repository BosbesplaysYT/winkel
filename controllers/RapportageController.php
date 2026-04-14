<?php
class RapportageController extends Controller
{
    private TransactieModel $transactieModel;

    public function __construct()
    {
        $this->transactieModel = new TransactieModel();
    }

    public function index(): void
    {
        $this->vereisRol('beheerder'); //vereis rol doet ook al vereis login, dus geen aparte vereisLogin nodig

        // Standaard: vandaag tonen
        $van = $_GET['van'] ?? date('Y-m-d');
        $tot = $_GET['tot'] ?? date('Y-m-d');

        $transacties  = $this->transactieModel->getRapportage($van, $tot);
        $samenvatting = $this->transactieModel->getSamenvatting($van, $tot);

        $this->toonView('rapportage', [
            'gebruikersnaam' => $_SESSION['gebruikersnaam'],
            'rol'            => $_SESSION['rol'],
            'transacties'    => $transacties,
            'samenvatting'   => $samenvatting,
            'van'            => $van,
            'tot'            => $tot,
        ]);
    }
}
