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
            font-size: 1rem;
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
            letter-spacing: 0.05em;
        }

        .product-tabel td {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.95rem;
        }

        .product-tabel tr:last-child td {
            border-bottom: none;
        }

        .product-tabel tr:hover td {
            background: #f9f9f9;
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

        .import-sectie h2 {
            margin-top: 0;
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

        .upload-rij input[type="file"] {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
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

        <!-- CSV Import -->
        <div class="import-sectie">
            <h2>Nieuwe voorraad importeren</h2>
            <p style="color: #555; margin-bottom: 0.5rem;">
                Upload een CSV-bestand met puntkomma (;) als scheidingsteken en de volgende kolommen:
            </p>
            <div class="csv-voorbeeld">
                artikelnummer;artikelnaam;artikelgroep;prijs;santal<br>
                1001;Volle melk 1L;Zuivel;1,29;50<br>
                1002;Kaas 500g;Zuivel;3,49;30
            </div>
            <form action="index.php?controller=voorraad&action=importeer" method="POST" enctype="multipart/form-data">
                <div class="upload-rij">
                    <input type="file" name="csv_bestand" accept=".csv" required>
                    <button type="submit" class="knop-primair" style="width: auto;">Importeren</button>
                </div>
            </form>
        </div>

        <!-- Voorraad per groep -->
        <h2 style="margin-bottom: 1rem;">Voorraad per artikelgroep</h2>

        <?php if (empty($voorraad)): ?>
            <p style="color: #999;">Er zijn nog geen producten in het systeem.</p>
        <?php else: ?>
            <div class="voorraad-grid">
                <?php foreach ($voorraad as $groep => $producten): ?>
                    <div class="groep-kaart">
                        <div class="groep-header">
                            <span><?= htmlspecialchars($groep) ?></span>
                            <span class="badge"><?= count($producten) ?> product(en)</span>
                        </div>
                        <table class="product-tabel">
                            <thead>
                                <tr>
                                    <th>Artikelnummer</th>
                                    <th>Naam</th>
                                    <th>Prijs</th>
                                    <th>Op voorraad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($producten as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['artikelnummer']) ?></td>
                                        <td><?= htmlspecialchars($p['artikelnaam']) ?></td>
                                        <td>€ <?= number_format($p['prijs'], 2, ',', '.') ?></td>
                                        <td class="<?= $p['santal'] <= 5 ? 'laag-voorraad' : '' ?>">
                                            <?= (int)$p['santal'] ?>
                                            <?= $p['santal'] <= 5 ? ' ⚠️' : '' ?>
                                        </td>
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