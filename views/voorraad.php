<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voorraadbeheer – Vaatje Buskruit</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .voorraad-grid {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .groep-kaart {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .groep-header {
            background: var(--donker-groen);
            color: white;
            padding: 0.75rem 1.25rem;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .groep-header span.badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .product-tabel {
            width: 100%;
            border-collapse: collapse;
        }

        .product-tabel th {
            text-align: left;
            padding: 0.6rem 1.25rem;
            font-size: 0.8rem;
            color: #666;
            border-bottom: 1px solid #eee;
            text-transform: uppercase;
        }

        .product-tabel td {
            padding: 0.5rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.95rem;
        }

        /* Input styling in tabel */
        .input-edit {
            border: 1px solid #ddd;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: inherit;
            font-size: 0.9rem;
            width: 100%;
        }

        .input-edit:focus {
            border-color: var(--donker-groen);
            outline: none;
        }

        .col-sm {
            width: 80px;
        }

        .knop-opslaan {
            background: #27ae60;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .knop-opslaan:hover {
            background: #219150;
        }

        .laag-voorraad {
            color: #c0392b;
            font-weight: bold;
        }

        .import-sectie {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .melding {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .melding.succes {
            background: #d4edda;
            color: #155724;
        }

        .melding.fout {
            background: #f8d7da;
            color: #721c24;
        }

        .csv-voorbeeld {
            background: #f8f9fa;
            border-left: 3px solid var(--donker-groen);
            padding: 0.75rem 1rem;
            font-family: monospace;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            border-radius: 0 6px 6px 0;
        }

        .upload-rij {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            margin-top: 0.75rem;
        }
    </style>
</head>

<body>
    <nav>
        <span class="nav-naam">Vaatje Buskruit</span>
        <div class="nav-rechts">
            <span><?= htmlspecialchars($gebruikersnaam) ?></span>
            <span class="nav-rol"><?= htmlspecialchars($rol) ?></span>
            <a href="index.php?controller=rapportage&action=index" class="knop-secundair">Rapportage</a>
            <a href="index.php?controller=dashboard&action=index" class="knop-secundair">Kassa</a>
            <a href="index.php?controller=auth&action=logout" class="knop-secundair">Uitloggen</a>
        </div>
    </nav>

    <main style="padding: 1.5rem;">
        <?php if (!empty($melding)): ?>
            <div class="melding succes"><?= htmlspecialchars($melding) ?></div>
        <?php endif; ?>
        <?php if (!empty($fout)): ?>
            <div class="melding fout"><?= htmlspecialchars($fout) ?></div>
        <?php endif; ?>

        <div class="import-sectie">
            <h2>Nieuwe voorraad importeren</h2>
            <p style="color: #555; margin-bottom: 0.5rem;">Upload een CSV-bestand (artikelnummer;artikelnaam;artikelgroep;prijs;santal)</p>
            <form action="index.php?controller=voorraad&action=importeer" method="POST" enctype="multipart/form-data">
                <div class="upload-rij">
                    <input type="file" name="csv_bestand" accept=".csv" required>
                    <button type="submit" class="knop-primair" style="width: auto;">Importeren</button>
                </div>
            </form>
        </div>

        <h2 style="margin-bottom: 1rem;">Voorraadbeheer</h2>

        <?php if (empty($voorraad)): ?>
            <p style="color: #999;">Er zijn nog geen producten in het systeem.</p>
        <?php else: ?>
            <div class="voorraad-grid">
                <?php foreach ($voorraad as $groep => $producten): ?>
                    <div class="groep-kaart">
                        <div class="groep-header">
                            <span><?= htmlspecialchars($groep) ?></span>
                            <span class="badge">
                                <?php
                                $count = count($producten);
                                echo $count . ($count === 1 ? ' product' : ' producten');
                                ?>
                            </span>
                        </div>
                        <table class="product-tabel">
                            <thead>
                                <tr>
                                    <th>Art.Nr</th>
                                    <th>Naam</th>
                                    <th>Categorie</th>
                                    <th>Prijs (€)</th>
                                    <th>Aantal</th>
                                    <th>Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($producten as $p): ?>
                                    <tr>
                                        <form action="index.php?controller=voorraad&action=bewerk" method="POST">
                                            <input type="hidden" name="artikelnummer" value="<?= htmlspecialchars($p['artikelnummer']) ?>">
                                            <td><?= htmlspecialchars($p['artikelnummer']) ?></td>
                                            <td>
                                                <input type="text" name="artikelnaam" value="<?= htmlspecialchars($p['artikelnaam']) ?>" class="input-edit">
                                            </td>
                                            <td>
                                                <input type="text" name="artikelgroep" value="<?= htmlspecialchars($groep) ?>" class="input-edit">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="prijs" value="<?= number_format($p['prijs'], 2, '.', '') ?>" class="input-edit col-sm">
                                            </td>
                                            <td>
                                                <input type="number" name="santal" value="<?= (int)$p['santal'] ?>" class="input-edit col-sm <?= $p['santal'] <= 5 ? 'laag-voorraad' : '' ?>">
                                            </td>
                                            <td>
                                                <button type="submit" class="knop-opslaan">Opslaan</button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>