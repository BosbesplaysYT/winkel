<?php
class GebruikerModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Zoek een gebruiker op via gebruikersnaam.
     * Geeft een associatieve array terug of null als niet gevonden.
     */
    public function vindOpGebruikersnaam(string $gebruikersnaam): ?array
    {
        $sql  = 'SELECT id, gebruikersnaam, wachtwoord_hash, rol FROM gebruiker WHERE gebruikersnaam = :gebruikersnaam LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gebruikersnaam' => $gebruikersnaam]);

        $resultaat = $stmt->fetch();

        // fetch() geeft false terug als er niets gevonden is; zet om naar null
        return $resultaat ?: null;
    }

    /**
     * Controleer of het opgegeven wachtwoord klopt bij de hash uit de database.
     */
    public function wachtwoordKlopt(string $wachtwoord, string $hash): bool
    {
        return password_verify($wachtwoord, $hash);
    }
}
