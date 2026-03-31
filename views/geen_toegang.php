<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geen toegang – Vaatje Buskruit</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .kaart {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            max-width: 380px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        h1 { font-size: 1.2rem; color: #c0392b; margin-bottom: 0.75rem; }
        p  { font-size: 0.9rem; color: #555; margin-bottom: 1.5rem; }
        a  {
            display: inline-block;
            background: #2d6a4f;
            color: #fff;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
        }
        a:hover { background: #1b4332; }
    </style>
</head>
<body>
    <div class="kaart">
        <h1>Geen toegang</h1>
        <p>Je hebt niet de juiste rechten om deze pagina te bekijken.</p>
        <a href="index.php?controller=dashboard&action=index">Terug naar dashboard</a>
    </div>
</body>
</html>
