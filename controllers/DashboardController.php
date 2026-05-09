<?php
// controllers/DashboardController.php

class DashboardController
{

    public function index()
    {
        // V controllers/DashboardController.php -> metóda index()
        $user = $_SESSION['user'];
        $db = Database::connect();

        // Dynamická podmienka: Admin vidí všetko (1=1), Odberateľ len svoje id_odberatel
        if ($user['rola'] === 'Admin') {
            $where = "1=1";
            $params = [];
        } else {
            $where = "id_odberatel = ?";
            $params = [$user['id_odberatel']];
        }

        // 1. Štatistiky pre karty
            $stats = $db->prepare("SELECT 
                COUNT(*) as celkom,
                SUM(CASE WHEN stav = 'Nová' THEN 1 ELSE 0 END) as nove, -- Pridaný dĺžeň: 'Nová'
                SUM(celkova_suma) as suma_celkom
                FROM OBJEDNAVKA WHERE $where");

        $stats->execute($params);
        $s = $stats->fetch();

        // 2. Termínovník z tabuľky TERMIN (Prepojenie s objednávkami firmy)
          $stmtTerminy = $db->prepare("
                            SELECT t.datum_cas_od, t.stav as termin_stav, o.cislo_objednavky, o.nazov_projektu, o.id_objednavka
                            FROM TERMIN t
                            JOIN OBJEDNAVKA o ON t.OBJEDNAVKA_id_objednavka = o.id_objednavka
                            WHERE " . ($user['rola'] === 'Admin' ? "1=1" : "o.id_odberatel = ?") . "
                            AND t.datum_cas_od >= NOW()
                            ORDER BY t.datum_cas_od ASC 
                            LIMIT 5
                        ");

        // Spustenie dopytu s parametrom iba ak nie je Admin
        if ($user['rola'] === 'Admin') {
            $stmtTerminy->execute([]);
        } else {
            $stmtTerminy->execute([$user['id_odberatel']]);
        }
        $terminovnik = $stmtTerminy->fetchAll();

        $view = 'views/dashboard/index.php';
        require_once 'views/layouts/main.php';
    }
}
