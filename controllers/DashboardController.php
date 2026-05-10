<?php
// controllers/DashboardController.php

class DashboardController
{
    public function index()
    {
        $user = $_SESSION['user'];
        $db = Database::connect();

        // 1. Definícia interných zamestnancov
        $isStaff = in_array($user['rola'], ['Admin', 'Vyroba', 'Obchod', 'Logistika']);

        // Nastavenie WHERE podmienky
        if ($isStaff) {
            $where = "1=1";
            $params = [];
        } else {
            $where = "o.id_odberatel = ?";
            $params = [$user['id_odberatel']];
        }

        // 2. Štatistiky pre karty
        $statsSql = "SELECT 
                        COUNT(*) as celkom,
                        SUM(CASE WHEN o.stav = 'Nová' THEN 1 ELSE 0 END) as nove,
                        SUM(o.celkova_suma) as suma_celkom
                     FROM OBJEDNAVKA o 
                     WHERE " . ($isStaff ? "1=1" : "o.id_odberatel = ?");
        
        $stats = $db->prepare($statsSql);
        $stats->execute($params);
        $s = $stats->fetch();

        // 3. Termínovník (Najbližšie udalosti)
        $stmtTerminy = $db->prepare("
            SELECT 
                t.datum_cas_od, 
                t.stav as stav_terminu, 
                o.cislo_objednavky, 
                o.nazov_projektu, 
                o.stav as stav_objednavky,
                o.id_objednavka,
                odb.obchodny_nazov,
                o.updated_at
            FROM TERMIN t
            JOIN OBJEDNAVKA o ON t.OBJEDNAVKA_id_objednavka = o.id_objednavka
            LEFT JOIN ODBERATEL odb ON o.id_odberatel = odb.id_odberatel
            WHERE " . ($isStaff ? "1=1" : "o.id_odberatel = ?") . "
            AND t.stav != 'Zrušený' 
            AND (
                t.stav != 'Dokončený' 
                OR DATE(t.datum_cas_od) = CURDATE()
            )
            ORDER BY t.stav = 'Dokončený' ASC, t.datum_cas_od ASC 
            LIMIT 10
        ");

        $stmtTerminy->execute($params);
        $terminy = $stmtTerminy->fetchAll();

        if (!$terminy) $terminy = [];

        $view = 'views/dashboard/index.php';
        require_once 'views/layouts/main.php';
    }
}