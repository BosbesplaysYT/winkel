<?php
class TransactieModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function slaTransactieOp(int $gebruikerId, float $totaal, float $btw, array $items): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO transactie (gebruiker_id, datum_tijd, totaalbedrag, btw_bedrag)
                    VALUES (:uid, NOW(), :totaal, :btw)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':uid' => $gebruikerId, ':totaal' => $totaal, ':btw' => $btw]);
            $transactieId = $this->db->lastInsertId();

            $regelSql    = "INSERT INTO transactie_regel (transactie_id, product_artikelnummer, aantal, stuksprijs)
                            VALUES (:tid, :art, :aantal, :prijs)";
            $voorraadSql = "UPDATE product SET santal = santal - :aantal WHERE artikelnummer = :art";

            $stmtRegel    = $this->db->prepare($regelSql);
            $stmtVoorraad = $this->db->prepare($voorraadSql);

            foreach ($items as $artNummer => $data) {
                $stmtRegel->execute([
                    ':tid'    => $transactieId,
                    ':art'    => $artNummer,
                    ':aantal' => $data['aantal'],
                    ':prijs'  => $data['prijs'],
                ]);
                $stmtVoorraad->execute([
                    ':aantal' => $data['aantal'],
                    ':art'    => $artNummer,
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Geeft alle transacties met regels terug binnen een datumperiode.
     * Structuur: [ transactie_id => [ 'info' => [...], 'regels' => [...] ], ... ]
     */
    public function getRapportage(string $van, string $tot): array
    {
        // Haal transacties op in de opgegeven periode
        $sql = "SELECT t.id, t.datum_tijd, t.totaalbedrag, t.btw_bedrag,
                       u.gebruikersnaam
                FROM transactie t
                JOIN gebruiker u ON t.gebruiker_id = u.id
                WHERE DATE(t.datum_tijd) BETWEEN :van AND :tot
                ORDER BY t.datum_tijd DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':van' => $van, ':tot' => $tot]);
        $transacties = $stmt->fetchAll();

        if (empty($transacties)) {
            return [];
        }

        // Haal regels op voor deze transacties
        $ids         = implode(',', array_column($transacties, 'id'));
        $regelSql    = "SELECT tr.transactie_id, tr.aantal, tr.stuksprijs,
                               p.artikelnaam
                        FROM transactie_regel tr
                        JOIN product p ON tr.product_artikelnummer = p.artikelnummer
                        WHERE tr.transactie_id IN ($ids)";
        $regelStmt   = $this->db->query($regelSql);
        $regels      = $regelStmt->fetchAll();

        // Koppel regels aan transacties
        $resultaat = [];
        foreach ($transacties as $t) {
            $resultaat[$t['id']] = [
                'info'   => $t,
                'regels' => [],
            ];
        }
        foreach ($regels as $r) {
            $resultaat[$r['transactie_id']]['regels'][] = $r;
        }

        return $resultaat;
    }

    /**
     * Geeft totaalbedrag en totaal-BTW terug voor de gegeven periode.
     */
    public function getSamenvatting(string $van, string $tot): array
    {
        $sql = "SELECT
                    COUNT(*)          AS aantal_transacties,
                    COALESCE(SUM(totaalbedrag), 0) AS totaal_omzet,
                    COALESCE(SUM(btw_bedrag), 0)   AS totaal_btw
                FROM transactie
                WHERE DATE(datum_tijd) BETWEEN :van AND :tot";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':van' => $van, ':tot' => $tot]);
        return $stmt->fetch();
    }
}
