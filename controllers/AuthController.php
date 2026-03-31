<?php
class AuthController extends Controller
{
    private GebruikerModel $gebruikerModel;

    public function __construct()
    {
        $this->gebruikerModel = new GebruikerModel();
    }

    /**
     * Standaardactie: stuur direct naar login.
     */
    public function index(): void
    {
        $this->doorsturen('index.php?controller=auth&action=login');
    }

    /**
     * Toon de loginpagina (GET) of verwerk het loginformulier (POST).
     */
    public function login(): void
    {
        // Al ingelogd? Meteen doorsturen naar dashboard
        if (!empty($_SESSION['gebruiker_id'])) {
            $this->doorsturen('index.php?controller=dashboard&action=index');
        }

        $fout = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
            $wachtwoord     = $_POST['wachtwoord'] ?? '';

            // Basis-validatie: zijn de velden ingevuld?
            if ($gebruikersnaam === '' || $wachtwoord === '') {
                $fout = 'Vul je gebruikersnaam en wachtwoord in.';
            } else {
                $gebruiker = $this->gebruikerModel->vindOpGebruikersnaam($gebruikersnaam);

                // Controleer gebruiker én wachtwoord in één stap om timing-attacks te voorkomen
                if (
                    $gebruiker !== null &&
                    $this->gebruikerModel->wachtwoordKlopt($wachtwoord, $gebruiker['wachtwoord_hash'])
                ) {
                    // Vernieuw de sessie-ID na een succesvolle login (sessiefixatie voorkomen)
                    session_regenerate_id(true);

                    $_SESSION['gebruiker_id']   = $gebruiker['id'];
                    $_SESSION['gebruikersnaam'] = $gebruiker['gebruikersnaam'];
                    $_SESSION['rol']            = $gebruiker['rol'];

                    $this->doorsturen('index.php?controller=dashboard&action=index');
                } else {
                    // Geef een vage foutmelding: vertel niet welk veld fout is
                    $fout = 'Gebruikersnaam of wachtwoord is onjuist.';
                }
            }
        }

        $this->toonView('login', ['fout' => $fout]);
    }

    /**
     * Log de gebruiker uit en vernietig de sessie volledig.
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->doorsturen('index.php?controller=auth&action=login');
    }

    /**
     * Toon een pagina als de gebruiker geen toegang heeft tot een bepaalde rol.
     */
    public function geenToegang(): void
    {
        $this->vereisLogin();
        $this->toonView('geen_toegang');
    }
}
