<?php
class ProductModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function vindOpBarcode(string $barcode): ?array
    {
        $sql  = 'SELECT * FROM product WHERE artikelnummer = :code LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $barcode]);
        return $stmt->fetch() ?: null;
    }

    public function uitgebreidZoeken(string $term): array
    {
        $sql = "SELECT p.*, g.naam AS categorie_naam
                FROM product p
                JOIN artikelgroep g ON p.artikelgroep_id = g.id
                WHERE p.artikelnaam   LIKE :term1
                   OR p.artikelnummer LIKE :term2
                   OR g.naam          LIKE :term3";

        $stmt       = $this->db->prepare($sql);
        $zoekterm   = "%$term%";
        $stmt->execute([':term1' => $zoekterm, ':term2' => $zoekterm, ':term3' => $zoekterm]);
        return $stmt->fetchAll();
    }

    /**
     * Geeft alle producten terug, gegroepeerd per artikelgroep.
     * Structuur: [ ['groep' => 'Zuivel', 'producten' => [...]], ... ]
     */
    public function getVoorraadPerGroep(): array
    {
        $sql = "SELECT p.artikelnummer, p.artikelnaam, p.prijs, p.santal, g.naam AS groep
                FROM product p
                JOIN artikelgroep g ON p.artikelgroep_id = g.id
                ORDER BY g.naam, p.artikelnaam";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rijen = $stmt->fetchAll();

        // Groepeer in PHP
        $gegroepeerd = [];
        foreach ($rijen as $rij) {
            $groep = $rij['groep'];
            if (!isset($gegroepeerd[$groep])) {
                $gegroepeerd[$groep] = [];
            }
            $gegroepeerd[$groep][] = $rij;
        }

        return $gegroepeerd;
    }

    /**
     * Voegt een product in of werkt het bij (upsert via artikelnummer).
     * Geeft 'nieuw', 'bijgewerkt' of 'fout' terug.
     */
    public function importeerProduct(
        string $artikelnummer,
        string $artikelnaam,
        string $artikelgroep,
        float  $prijs,
        int    $santal
    ): string {
        try {
            // Zorg dat de artikelgroep bestaat
            $groepId = $this->vindOfMaakGroep($artikelgroep);

            // Bestaat dit product al?
            $checkSql  = 'SELECT COUNT(*) FROM product WHERE artikelnummer = :art';
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':art' => $artikelnummer]);
            $bestaat = (bool) $checkStmt->fetchColumn();

            if ($bestaat) {
                // Voorraad en prijs bijwerken
                $sql = "UPDATE product
                        SET artikelnaam = :naam, prijs = :prijs,
                            santal = santal + :santal, artikelgroep_id = :gid
                        WHERE artikelnummer = :art";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':naam'   => $artikelnaam,
                    ':prijs'  => $prijs,
                    ':santal' => $santal,
                    ':gid'    => $groepId,
                    ':art'    => $artikelnummer,
                ]);
                return 'bijgewerkt';
            } else {
                $sql = "INSERT INTO product (artikelnummer, artikelnaam, artikelgroep_id, prijs, santal)
                        VALUES (:art, :naam, :gid, :prijs, :santal)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':art'    => $artikelnummer,
                    ':naam'   => $artikelnaam,
                    ':gid'    => $groepId,
                    ':prijs'  => $prijs,
                    ':santal' => $santal,
                ]);
                return 'nieuw';
            }
        } catch (Exception $e) {
            return 'fout';
        }
    }

    /**
     * Zoekt een artikelgroep op naam of maakt hem aan als hij nog niet bestaat.
     */
    private function vindOfMaakGroep(string $naam): int
    {
        $sql  = 'SELECT id FROM artikelgroep WHERE naam = :naam LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':naam' => $naam]);
        $rij = $stmt->fetch();

        if ($rij) {
            return (int) $rij['id'];
        }

        $sql  = 'INSERT INTO artikelgroep (naam) VALUES (:naam)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':naam' => $naam]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProductHandmatig(string $art, string $naam, string $groep, float $prijs, int $santal): bool
    {
        try {
            $groepId = $this->vindOfMaakGroep($groep);
            $sql = "UPDATE product 
                SET artikelnaam = :naam, artikelgroep_id = :gid, prijs = :prijs, santal = :santal 
                WHERE artikelnummer = :art";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':naam'   => $naam,
                ':gid'    => $groepId,
                ':prijs'  => $prijs,
                ':santal' => $santal,
                ':art'    => $art
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
