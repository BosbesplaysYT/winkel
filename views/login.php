<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen – Vaatje Buskruit</title>
    <link rel="stylesheet" href="styles/style.css">
</head>

<body class="container-gecentreerd">

    <div class="kaart">
        <header style="text-align: center; margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0.2rem;">Vaatje Buskruit</h1>
            <p class="helptekst">Kassasysteem & Voorraadbeheer</p>
        </header>

        <h2>Inloggen</h2>

        <?php if ($fout !== null): ?>
            <div class="foutmelding">
                <?= htmlspecialchars($fout) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?controller=auth&action=login" novalidate>
            <div class="form-groep">
                <label for="gebruikersnaam">Gebruikersnaam</label>
                <input type="text" id="gebruikersnaam" name="gebruikersnaam" autocomplete="username" autofocus
                    value="<?= htmlspecialchars($_POST['gebruikersnaam'] ?? '') ?>">
            </div>

            <div class="form-groep">
                <label for="wachtwoord">Wachtwoord</label>
                <input type="password" id="wachtwoord" name="wachtwoord" autocomplete="current-password">
            </div>

            <button type="submit" class="knop-primair">Inloggen</button>
        </form>

        <p class="helptekst" style="text-align: center; margin-top: 2rem;">
            Neem contact op met de beheerder als je niet kunt inloggen.
        </p>
    </div>

</body>

</html>