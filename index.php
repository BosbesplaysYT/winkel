<?php
session_start();

// Autoloader – laadt klassen automatisch op basis van mappenstructuur
spl_autoload_register(function (string $class): void {
    $directories = ['config', 'core', 'models', 'controllers'];
    foreach ($directories as $dir) {
        $path = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Bepaal welke controller en actie uitgevoerd moeten worden
// Verwacht URL-formaat: ?controller=auth&action=login
$controllerNaam = $_GET['controller'] ?? 'auth';
$actieNaam      = $_GET['action']     ?? 'index';

// Zet eerste letter op hoofdletter en voeg 'Controller' toe
$controllerKlasse = ucfirst($controllerNaam) . 'Controller';

if (class_exists($controllerKlasse)) {
    $controller = new $controllerKlasse();

    if (method_exists($controller, $actieNaam)) {
        $controller->$actieNaam();
    } else {
        http_response_code(404);
        die('Actie niet gevonden: ' . htmlspecialchars($actieNaam));
    }
} else {
    http_response_code(404);
    die('Controller niet gevonden: ' . htmlspecialchars($controllerKlasse));
}
