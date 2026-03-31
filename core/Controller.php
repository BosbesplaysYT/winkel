<?php
class Controller
{
    /**
     * Laad een view-bestand en geef optioneel data mee.
     * De data-array wordt uitgevouwen als losse variabelen in de view.
     */
    protected function toonView(string $view, array $data = []): void
    {
        // Maak alle sleutels uit $data beschikbaar als variabelen (bijv. $fout, $gebruiker)
        extract($data, EXTR_SKIP);

        $pad = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($pad)) {
            http_response_code(404);
            die('View niet gevonden: ' . htmlspecialchars($view));
        }

        require $pad;
    }

    /**
     * Stuur de gebruiker door naar een andere pagina.
     */
    protected function doorsturen(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Controleer of de gebruiker is ingelogd.
     * Stuur anders door naar de loginpagina.
     */
    protected function vereisLogin(): void
    {
        if (empty($_SESSION['gebruiker_id'])) {
            $this->doorsturen('index.php?controller=auth&action=login');
        }
    }

    /**
     * Controleer of de ingelogde gebruiker een beheerder is.
     * Stuur anders door naar het dashboard met een foutmelding.
     */
    protected function vereisRol(string $rol): void
    {
        $this->vereisLogin();

        if (($_SESSION['rol'] ?? '') !== $rol) {
            $this->doorsturen('index.php?controller=auth&action=geenToegang');
        }
    }
}
