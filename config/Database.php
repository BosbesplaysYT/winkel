<?php
class Database
{
    private static ?PDO $instance = null;

    // Database-instellingen – pas aan naar jouw XAMPP-configuratie
    private const HOST     = 'localhost';
    private const DB_NAME  = 'vaatje_buskruit';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const CHARSET  = 'utf8mb4';

    // Privé constructor zodat er geen losse instanties aangemaakt kunnen worden
    private function __construct() {}

    /**
     * Geeft altijd dezelfde PDO-verbinding terug (Singleton-patroon).
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::HOST,
                self::DB_NAME,
                self::CHARSET
            );

            $opties = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::USERNAME, self::PASSWORD, $opties);
            } catch (PDOException $e) {
                // Laat nooit de echte foutmelding zien in productie
                error_log('Databaseverbinding mislukt: ' . $e->getMessage());
                die('Kan geen verbinding maken met de database. Controleer de instellingen.');
            }
        }

        return self::$instance;
    }
}
