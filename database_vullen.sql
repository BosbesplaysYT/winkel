-- ============================================================
-- Vaatje Buskruit – Kassasysteem & Voorraadbeheer
-- Database aanmaak + testdata
-- ============================================================

CREATE DATABASE IF NOT EXISTS winkel
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE winkel;

-- ------------------------------------------------------------
-- Tabel: gebruiker
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gebruiker (
    id              INT             NOT NULL AUTO_INCREMENT,
    gebruikersnaam  VARCHAR(100)    NOT NULL UNIQUE,
    wachtwoord_hash VARCHAR(255)    NOT NULL,
    rol             ENUM('kassamedewerker', 'beheerder') NOT NULL DEFAULT 'kassamedewerker',
    aangemaakt_op   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel: artikelgroep
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS artikelgroep (
    id      INT             NOT NULL AUTO_INCREMENT,
    naam    VARCHAR(100)    NOT NULL UNIQUE,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel: product
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product (
    artikelnummer   VARCHAR(50)     NOT NULL,
    artikelnaam     VARCHAR(150)    NOT NULL,
    omschrijving    TEXT,
    leverancier     VARCHAR(150),
    artikelgroep_id INT             NOT NULL,
    eenheid         VARCHAR(30)     NOT NULL DEFAULT 'stuk',
    prijs           DECIMAL(10,2)   NOT NULL,
    santal          INT             NOT NULL DEFAULT 0,
    PRIMARY KEY (artikelnummer),
    CONSTRAINT fk_product_artikelgroep
        FOREIGN KEY (artikelgroep_id) REFERENCES artikelgroep (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel: transactie
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactie (
    id              INT             NOT NULL AUTO_INCREMENT,
    gebruiker_id    INT             NOT NULL,
    datum_tijd      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    totaalbedrag    DECIMAL(10,2)   NOT NULL,
    btw_bedrag      DECIMAL(10,2)   NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_transactie_gebruiker
        FOREIGN KEY (gebruiker_id) REFERENCES gebruiker (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel: transactie_regel
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactie_regel (
    id                      INT             NOT NULL AUTO_INCREMENT,
    transactie_id           INT             NOT NULL,
    product_artikelnummer   VARCHAR(50)     NOT NULL,
    aantal                  INT             NOT NULL,
    stuksprijs              DECIMAL(10,2)   NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_transactieregel_transactie
        FOREIGN KEY (transactie_id) REFERENCES transactie (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_transactieregel_product
        FOREIGN KEY (product_artikelnummer) REFERENCES product (artikelnummer)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ============================================================
-- TESTDATA
-- ============================================================

-- ------------------------------------------------------------
-- Gebruikers
-- Wachtwoorden zijn gehashed met bcrypt (plaintext staat erbij als commentaar)
-- Plaintext wachtwoorden: beheerder → Admin123!, kassa1 → Kassa123!, kassa2 → Kassa123!
-- ------------------------------------------------------------
INSERT INTO gebruiker (gebruikersnaam, wachtwoord_hash, rol, aangemaakt_op) VALUES
    ('beheerder',   '$2y$12$eImiTXuWVxfM37uY4JANjOe5XscKhW28a4X6tMpCyus8yW/jKUe8a', 'beheerder',          '2025-01-01 08:00:00'),
    ('kassa1',      '$2y$12$Xu9OtXKfIdNuJveLUJjW4OEf84mXIvE0pYXMkFdPp0dL4/HJ5B.Jm', 'kassamedewerker',    '2025-01-01 08:05:00'),
    ('kassa2',      '$2y$12$3kOHlp0CbJy7ELZjN6C1GekOm2D5wB9V1TfZ3gA8Wc5dXQ2jR7Ike', 'kassamedewerker',    '2025-03-01 09:00:00');

-- ------------------------------------------------------------
-- Artikelgroepen
-- ------------------------------------------------------------
INSERT INTO artikelgroep (naam) VALUES
    ('Zuivel'),
    ('Brood & Banket'),
    ('Groente & Fruit'),
    ('Vlees & Vleeswaren'),
    ('Dranken'),
    ('Houdbaar'),
    ('Snoep & Koek'),
    ('Persoonlijke verzorging');

-- ------------------------------------------------------------
-- Producten
-- artikelgroep_id: 1=Zuivel, 2=Brood & Banket, 3=Groente & Fruit,
--                  4=Vlees & Vleeswaren, 5=Dranken, 6=Houdbaar,
--                  7=Snoep & Koek, 8=Persoonlijke verzorging
-- ------------------------------------------------------------
INSERT INTO product (artikelnummer, artikelnaam, omschrijving, leverancier, artikelgroep_id, eenheid, prijs, santal) VALUES
    -- Zuivel
    ('1000001', 'Volle melk 1L',               'Verse volle melk, gepasteuriseerd',        'FrieslandCampina',     1, 'liter',    1.09,   80),
    ('1000002', 'Halfvolle melk 1L',            'Verse halfvolle melk, gepasteuriseerd',    'FrieslandCampina',     1, 'liter',    1.05,   65),
    ('1000003', 'Roomboter 250g',               'Ongezouten roomboter',                     'Kerrygold',            1, 'stuk',     2.49,   40),
    ('1000004', 'Kaas Goudse 48+ 500g',         'Gesneden Goudse kaas 48+',                 'Vaatje Eigen Merk',    1, 'stuk',     3.99,   35),
    ('1000005', 'Griekse yoghurt 500g',         'Volle Griekse yoghurt',                    'Olympus',              1, 'stuk',     2.19,   25),

    -- Brood & Banket
    ('2000001', 'Wit brood heel',               'Heel wit cafébrood 800g',                  'Bakkerij de Korst',    2, 'stuk',     2.29,   20),
    ('2000002', 'Volkorenbrood heel',           'Heel volkorenbrood 800g',                  'Bakkerij de Korst',    2, 'stuk',     2.49,   15),
    ('2000003', 'Croissant 4 stuks',            'Boter croissants, 4 per verpakking',       'Bake & More',          2, 'stuk',     2.99,   30),
    ('2000004', 'Appelflap',                    'Verse appelflap 130g',                     'Bakkerij de Korst',    2, 'stuk',     1.39,   25),

    -- Groente & Fruit
    ('3000001', 'Appels Elstar 1kg',            'Verse Elstar appels, seizoensproduct',     'Fruitmand BV',         3, 'kg',       2.49,   50),
    ('3000002', 'Bananen 1kg',                  'Rijpe bananen uit Ecuador',                'Chiquita',             3, 'kg',       1.79,   40),
    ('3000003', 'Tomaten zak 500g',             'Trostomaten rood',                         'Lokale Boer',          3, 'stuk',     1.29,   30),
    ('3000004', 'IJsbergsla',                   'IJsbergsla, losse krop',                   'Lokale Boer',          3, 'stuk',     0.99,   20),

    -- Vlees & Vleeswaren
    ('4000001', 'Kipfilet 500g',                'Verse kipfilet, scharrelkip',              'Van Beek Kip',         4, 'stuk',     4.99,   20),
    ('4000002', 'Rundergehakt 500g',            'Mager rundergehakt 5% vet',                'Slagerij Kruit',       4, 'stuk',     4.49,   18),
    ('4000003', 'Boterhamworst 200g',           'Gesneden boterhamworst',                   'Unox',                 4, 'stuk',     1.89,   25),
    ('4000004', 'Gerookte zalm 100g',           'Noorse gerookte zalm',                     'SeaSelect',            4, 'stuk',     3.49,   12),

    -- Dranken
    ('5000001', 'Cola 1,5L',                    'Cola light geen suiker',                   'Coca-Cola',            5, 'fles',     1.99,   60),
    ('5000002', 'Jus d'orange 1L',              'Vers geperst sinaasappelsap',              'Innocent',             5, 'fles',     2.49,   30),
    ('5000003', 'Mineraalwater 1,5L',           'Plat bronwater',                           'Spa',                  5, 'fles',     0.79,   80),
    ('5000004', 'Bier 6-pack 330ml',            'Pils 5% alcohol, 6x330ml',                'Heineken',             5, 'stuk',     5.49,   45),

    -- Houdbaar
    ('6000001', 'Spaghetti 500g',               'Droge pasta spaghetti nr. 5',              'Barilla',              6, 'stuk',     1.29,   55),
    ('6000002', 'Tomatenblokjes 400g',          'Gehakte tomaten in blik',                  'Heinz',                6, 'blik',     0.89,   70),
    ('6000003', 'Rijst Basmati 1kg',            'Premium Basmati rijst',                    'Lassie',               6, 'stuk',     2.99,   40),
    ('6000004', 'Olijfolie extra vierge 500ml', 'Koudgeperste olijfolie uit Italië',        'Bertolli',             6, 'fles',     4.99,   25),

    -- Snoep & Koek
    ('7000001', 'Chocolade melk 100g',          'Melkchocolade reep',                       'Verkade',              7, 'stuk',     1.49,   60),
    ('7000002', 'Stroopwafels 8 stuks',         'Traditionele Goudse stroopwafels',         'Daelmans',             7, 'stuk',     1.99,   50),
    ('7000003', 'Chips naturel 200g',           'Aardappelchips naturel',                   'Lay\'s',               7, 'stuk',     2.29,   45),

    -- Persoonlijke verzorging
    ('8000001', 'Shampoo 250ml',                'Anti-roos shampoo',                        'Head & Shoulders',     8, 'fles',     4.49,   20),
    ('8000002', 'Tandpasta 75ml',               'Whitening tandpasta fluoride',             'Colgate',              8, 'tube',     2.99,   30),
    ('8000003', 'Zeep vloeibaar 250ml',         'Handzeep met aloë vera',                   'Dove',                 8, 'fles',     3.29,   25);

-- ------------------------------------------------------------
-- Transacties (gebruiker_id 2 = kassa1, 3 = kassa2)
-- BTW-tarief: laag 9% (voedsel/dranken), hoog 21% (overig)
-- Totaalbedrag is inclusief BTW; btw_bedrag is het BTW-deel
-- ------------------------------------------------------------
INSERT INTO transactie (gebruiker_id, datum_tijd, totaalbedrag, btw_bedrag) VALUES
    (2, '2026-03-20 09:14:22', 6.86,  0.57),   -- id 1
    (2, '2026-03-20 10:02:05', 12.25, 1.00),   -- id 2
    (3, '2026-03-21 11:30:40', 8.67,  0.71),   -- id 3
    (2, '2026-03-22 14:15:00', 15.93, 1.30),   -- id 4
    (3, '2026-03-24 08:55:18', 5.57,  1.05);   -- id 5

-- ------------------------------------------------------------
-- Transactie-regels
-- ------------------------------------------------------------
INSERT INTO transactie_regel (transactie_id, product_artikelnummer, aantal, stuksprijs) VALUES
    -- Transactie 1: melk + brood + appels
    (1, '1000001', 2, 1.09),
    (1, '2000001', 1, 2.29),
    (1, '3000001', 1, 2.49),

    -- Transactie 2: kipfilet + pasta + tomatenblokjes + water
    (2, '4000001', 1, 4.99),
    (2, '6000001', 2, 1.29),
    (2, '6000002', 2, 0.89),
    (2, '5000003', 1, 0.79),

    -- Transactie 3: yoghurt + croissants + bananen + jus
    (3, '1000005', 1, 2.19),
    (3, '2000003', 1, 2.99),
    (3, '3000002', 1, 1.79),
    (3, '5000002', 1, 2.49),    -- let op: totaal afgerond incl. BTW

    -- Transactie 4: kaas + gehakt + rijst + olijfolie + cola
    (4, '1000004', 1, 3.99),
    (4, '4000002', 1, 4.49),
    (4, '6000003', 1, 2.99),
    (4, '6000004', 1, 4.99),
    (4, '5000001', 1, 1.99),    -- let op: dit is een grotere boodschap

    -- Transactie 5: shampoo + chips + chocolade
    (5, '8000001', 1, 4.49),
    (5, '7000003', 1, 2.29),
    (5, '7000001', 1, 1.49);


-- ============================================================
-- Controleer resultaten
-- ============================================================

-- Overzicht producten per artikelgroep
SELECT ag.naam AS artikelgroep, COUNT(p.artikelnummer) AS aantal_producten
FROM artikelgroep ag
LEFT JOIN product p ON p.artikelgroep_id = ag.id
GROUP BY ag.naam
ORDER BY ag.naam;

-- Overzicht transacties met kassamedewerkernaam
SELECT t.id, g.gebruikersnaam, t.datum_tijd, t.totaalbedrag, t.btw_bedrag
FROM transactie t
JOIN gebruiker g ON g.id = t.gebruiker_id
ORDER BY t.datum_tijd;