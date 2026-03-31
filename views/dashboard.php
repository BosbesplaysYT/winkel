<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kassa – Vaatje Buskruit</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/kassa.css">
    <style>
        .bon-item-acties {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .knop-aantal {
            background: none;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 2rem;
            height: 2rem;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            padding: 0;
        }

        .knop-aantal:hover {
            background: #f0f0f0;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.verborgen {
            display: none;
        }

        /* Bonnetje */
        .bonnetje {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            font-family: 'Courier New', Courier, monospace;
        }

        .bonnetje-winkel {
            text-align: center;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.2rem;
        }

        .bonnetje-meta {
            text-align: center;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .bonnetje hr {
            border: none;
            border-top: 1px dashed #aaa;
            margin: 0.75rem 0;
        }

        .bonnetje-rij {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .bonnetje-rij.totaal-rij {
            font-weight: bold;
            font-size: 1.05rem;
            margin-top: 0.25rem;
        }

        .bonnetje-rij.btw-rij {
            font-size: 0.8rem;
            color: #666;
        }

        .bonnetje-dank {
            text-align: center;
            font-size: 0.85rem;
            margin-top: 1rem;
            color: #555;
        }

        .modal-knoppen {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .modal-knoppen button {
            flex: 1;
        }

        /* Print: verberg alles behalve het bonnetje */
        @media print {
            body>* {
                display: none !important;
            }

            .modal-overlay {
                display: flex !important;
                position: static !important;
                background: none !important;
            }

            .bonnetje {
                box-shadow: none;
                max-width: 100%;
            }

            .modal-knoppen {
                display: none !important;
            }
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
            <a href="index.php?controller=rapportage&action=index" class="knop-secundair">Rapportage</a>
            <a href="index.php?controller=auth&action=logout" class="knop-secundair">Uitloggen</a>
        </div>
    </nav>

    <main style="padding: 1.5rem;">

        <?php if (!empty($fout)): ?>
            <div style="background:#f8d7da;color:#721c24;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">
                <?= htmlspecialchars($fout) ?>
            </div>
        <?php endif; ?>

        <div class="kassa-container">

            <!-- Scan & zoek -->
            <section class="scan-sectie">
                <h2>Product toevoegen</h2>
                <form action="index.php?controller=dashboard&action=voegToe" method="POST">
                    <div class="form-groep">
                        <label for="barcode">Scan barcode of typ artikelnummer</label>
                        <input type="text" id="barcode" name="barcode" autofocus
                            placeholder="Scan hier..." autocomplete="off">
                    </div>
                    <button type="submit" class="knop-primair">Toevoegen aan bon</button>
                </form>

                <form action="index.php" method="GET" style="margin-top: 2rem;">
                    <input type="hidden" name="controller" value="dashboard">
                    <input type="hidden" name="action" value="zoeken">
                    <div class="form-groep">
                        <label>Handmatig zoeken (Naam, Code of Groep)</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" name="zoekterm" placeholder="Bijv. Melk of Zuivel...">
                            <button type="submit" class="knop-primair" style="width: auto;">Zoek</button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($zoekResultaten)): ?>
                    <div class="zoek-resultaten">
                        <?php foreach ($zoekResultaten as $res): ?>
                            <div class="bon-item" style="align-items: center;">
                                <span>
                                    <strong><?= htmlspecialchars($res['artikelnaam']) ?></strong>
                                    (<?= htmlspecialchars($res['categorie_naam']) ?>)
                                    <small style="color:#888;"> – € <?= number_format($res['prijs'], 2, ',', '.') ?></small>
                                </span>
                                <form action="index.php?controller=dashboard&action=voegToe" method="POST">
                                    <input type="hidden" name="barcode" value="<?= htmlspecialchars($res['artikelnummer']) ?>">
                                    <button type="submit" class="knop-primair"
                                        style="background: var(--donker-groen); color: white; width: auto; padding: 0.4rem 0.9rem;">+</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Huidige bon -->
            <section class="bon-sectie">
                <h2>Huidige Bon</h2>

                <div class="bon-items">
                    <?php if (empty($mandje)): ?>
                        <p style="color: #999; text-align: center; margin-top: 2rem;">De bon is nog leeg.</p>
                    <?php else: ?>
                        <?php foreach ($mandje as $artNr => $item): ?>
                            <div class="bon-item">
                                <div style="flex:1; min-width:0; font-size:0.95rem;">
                                    <?= htmlspecialchars($item['naam']) ?>
                                </div>

                                <div class="bon-item-acties">
                                    <!-- Plus: aantal verhogen -->
                                    <form action="index.php?controller=dashboard&action=verhoogAantal" method="POST" style="margin:0;">
                                        <input type="hidden" name="artikelnummer" value="<?= htmlspecialchars($artNr) ?>">
                                        <button type="submit" class="knop-aantal"
                                            <?= ($item['aantal'] >= $item['max_voorraad']) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                                            +
                                        </button>
                                    </form>

                                    <span style="min-width:1.4rem; text-align:center; font-weight:bold; font-size:0.95rem;">
                                        <?= (int)$item['aantal'] ?>×
                                    </span>

                                    <!-- Min/verwijder: bij 1 wordt het een ✕ (verwijderen) -->
                                    <form action="index.php?controller=dashboard&action=<?= $item['aantal'] > 1 ? 'verlaagAantal' : 'verwijderItem' ?>" method="POST" style="margin:0;">
                                        <input type="hidden" name="artikelnummer" value="<?= htmlspecialchars($artNr) ?>">
                                        <button type="submit" class="knop-aantal" title="<?= $item['aantal'] > 1 ? 'Één minder' : 'Verwijder' ?>"
                                            style="<?= $item['aantal'] <= 1 ? 'border-color:#e74c3c;color:#e74c3c;' : '' ?>">
                                            <?= $item['aantal'] > 1 ? '−' : '✕' ?>
                                        </button>
                                    </form>

                                    <span style="min-width:4.5rem; text-align:right; font-size:0.95rem;">
                                        € <?= number_format($item['prijs'] * $item['aantal'], 2, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="totaal-blok">
                    <div style="display: flex; justify-content: space-between; font-size: 1.5rem; font-weight: bold;">
                        <span>Totaal</span>
                        <span>€ <?= number_format($totaal, 2, ',', '.') ?></span>
                    </div>
                    <p class="btw-tekst">
                        Waarvan BTW (21%): € <?= number_format($totaal / 121 * 21, 2, ',', '.') ?>
                    </p>

                    <div class="bon-acties">
                        <a href="index.php?controller=dashboard&action=leegMandje"
                            class="knop-primair knop-wissen"
                            style="text-align: center; text-decoration: none; line-height: 20px;">
                            Wissen
                        </a>
                        <form action="index.php?controller=dashboard&action=afrekenen" method="POST">
                            <button type="submit" class="knop-primair knop-afrekenen">Afrekenen</button>
                        </form>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- ══════════════════════════════════════════
         Bonnetje-modal – verschijnt na afrekenen
    ══════════════════════════════════════════ -->
    <?php if (!empty($bon)): ?>
        <div class="modal-overlay" id="bonModal">
            <div class="bonnetje">
                <div class="bonnetje-winkel">Vaatje Buskruit</div>
                <div class="bonnetje-meta">
                    <?= htmlspecialchars($bon['datum_tijd']) ?><br>
                    Kassier: <?= htmlspecialchars($bon['kassier']) ?>
                </div>
                <hr>

                <?php foreach ($bon['items'] as $item): ?>
                    <div class="bonnetje-rij">
                        <span><?= (int)$item['aantal'] ?>× <?= htmlspecialchars($item['naam']) ?></span>
                        <span>€ <?= number_format($item['prijs'] * $item['aantal'], 2, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>

                <hr>
                <div class="bonnetje-rij totaal-rij">
                    <span>TOTAAL</span>
                    <span>€ <?= number_format($bon['totaal'], 2, ',', '.') ?></span>
                </div>
                <div class="bonnetje-rij btw-rij">
                    <span>Waarvan BTW (21%)</span>
                    <span>€ <?= number_format($bon['btw'], 2, ',', '.') ?></span>
                </div>

                <div class="bonnetje-dank">Bedankt voor uw aankoop!</div>

                <div class="modal-knoppen">
                    <button class="knop-primair" onclick="window.print()"
                        style="background: var(--donker-groen);">
                        🖨&nbsp;Afdrukken
                    </button>
                    <button class="knop-secundair"
                        onclick="document.getElementById('bonModal').classList.add('verborgen')"
                        style="background: #ccc; color: #333;">
                        Sluiten
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

</body>

</html>