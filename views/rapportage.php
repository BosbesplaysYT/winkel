<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapportage – Vaatje Buskruit</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .filter-balk {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-end;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-balk .form-groep {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .filter-balk label {
            font-size: 0.85rem;
            color: #555;
        }

        .filter-balk input[type="date"] {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .samenvatting-blok {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .samenvatting-kaart {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1.25rem;
            text-align: center;
        }

        .samenvatting-kaart .getal {
            font-size: 2rem;
            font-weight: bold;
            color: var(--donker-groen);
        }

        .samenvatting-kaart .label {
            font-size: 0.85rem;
            color: #888;
            margin-top: 0.25rem;
        }

        .transactie-kaart {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .transactie-header {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .transactie-header .datum {
            font-size: 0.85rem;
            color: #888;
        }

        .transactie-header .kassier {
            font-size: 0.85rem;
            color: #555;
        }

        .transactie-totaal {
            font-weight: bold;
            font-size: 1.05rem;
        }

        .regel-tabel {
            width: 100%;
            border-collapse: collapse;
        }

        .regel-tabel td {
            padding: 0.6rem 1.25rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f5f5f5;
            color: #444;
        }

        .regel-tabel tr:last-child td {
            border-bottom: none;
        }

        .regel-tabel .subtotaal {
            text-align: right;
        }

        .geen-data {
            text-align: center;
            color: #aaa;
            padding: 3rem 0;
        }
    </style>
</head>

<body>

    <nav>
        <span class="nav-naam">Vaatje Buskruit</span>
        <div class="nav-rechts">
            <span><?= htmlspecialchars($gebruikersnaam) ?></span>
            <span class="nav-rol"><?= htmlspecialchars($rol) ?></span>
            <?php if ($rol === 'beheerder'): ?>
                <a href="index.php?controller=voorraad&action=index" class="knop-secundair">Voorraadbeheer</a>
            <?php endif; ?>
            <a href="index.php?controller=dashboard&action=index" class="knop-secundair">Kassa</a>
            <a href="index.php?controller=auth&action=logout" class="knop-secundair">Uitloggen</a>
        </div>
    </nav>

    <main style="padding: 1.5rem;">
        <h2 style="margin-bottom: 1rem;">Verkooprapportage</h2>

        <!-- Datumfilter -->
        <form class="filter-balk" action="index.php" method="GET">
            <input type="hidden" name="controller" value="rapportage">
            <input type="hidden" name="action" value="index">
            <div class="form-groep">
                <label for="van">Van</label>
                <input type="date" id="van" name="van" value="<?= htmlspecialchars($van) ?>">
            </div>
            <div class="form-groep">
                <label for="tot">Tot en met</label>
                <input type="date" id="tot" name="tot" value="<?= htmlspecialchars($tot) ?>">
            </div>
            <button type="submit" class="knop-primair" style="width: auto;">Tonen</button>
        </form>

        <!-- Samenvatting -->
        <div class="samenvatting-blok">
            <div class="samenvatting-kaart">
                <div class="getal"><?= (int)$samenvatting['aantal_transacties'] ?></div>
                <div class="label">Transacties</div>
            </div>
            <div class="samenvatting-kaart">
                <div class="getal">€ <?= number_format($samenvatting['totaal_omzet'], 2, ',', '.') ?></div>
                <div class="label">Totale omzet (incl. BTW)</div>
            </div>
            <div class="samenvatting-kaart">
                <div class="getal">€ <?= number_format($samenvatting['totaal_btw'], 2, ',', '.') ?></div>
                <div class="label">Waarvan BTW (21%)</div>
            </div>
        </div>

        <!-- Transacties -->
        <?php if (empty($transacties)): ?>
            <div class="geen-data">Geen transacties gevonden voor deze periode.</div>
        <?php else: ?>
            <?php foreach ($transacties as $t): ?>
                <?php $info = $t['info']; ?>
                <div class="transactie-kaart">
                    <div class="transactie-header">
                        <div>
                            <span class="datum">
                                <?= date('d-m-Y H:i', strtotime($info['datum_tijd'])) ?>
                            </span>
                            <span class="kassier" style="margin-left: 1rem;">
                                Kassier: <?= htmlspecialchars($info['gebruikersnaam']) ?>
                            </span>
                        </div>
                        <div>
                            <span class="transactie-totaal">
                                € <?= number_format($info['totaalbedrag'], 2, ',', '.') ?>
                            </span>
                            <span style="font-size: 0.8rem; color: #999; margin-left: 0.5rem;">
                                (BTW: € <?= number_format($info['btw_bedrag'], 2, ',', '.') ?>)
                            </span>
                        </div>
                    </div>
                    <table class="regel-tabel">
                        <?php foreach ($t['regels'] as $r): ?>
                            <tr>
                                <td><?= (int)$r['aantal'] ?>× <?= htmlspecialchars($r['artikelnaam']) ?></td>
                                <td class="subtotaal">
                                    € <?= number_format($r['aantal'] * $r['stuksprijs'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>

</html>