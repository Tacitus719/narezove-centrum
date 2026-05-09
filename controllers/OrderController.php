<?php
// controllers/OrderController.php

class OrderController
{

    // 1. Zobrazenie formulára pre novú objednávku
    public function create()
    {
        $user = $_SESSION['user'];
        $db = Database::connect();

        // Vytiahneme materiály 
        $stmt = $db->query("SELECT * FROM MATERIAL WHERE is_active = 1 ORDER BY nazov_dekoru ASC");
        $materials = $db->query("SELECT * FROM MATERIAL WHERE is_active = 1 ORDER BY nazov_dekoru ASC")->fetchAll();

        // Vytiahneme hrany
        $stmtHrana = $db->query("SELECT * FROM ABS_HRANA WHERE is_active = 1 ORDER BY nazov ASC");
        $edges = $stmtHrana->fetchAll();

        $view = 'views/orders/create.php';
        require_once 'views/layouts/main.php';
    }

    // 2. Uloženie novej objednávky do databázy
    public function store()
    {
        $user = $_SESSION['user'];
        $db = Database::connect();

        $nazovProjektu = $_POST['nazov_projektu'] ?? 'Nová zákazka';
        $poznamka = $_POST['poznamka'] ?? '';
        $diely = $_POST['diely'] ?? [];

        try {
            $db->beginTransaction();

            // 1. Predbežné načítanie cien do pamäte (kvôli rýchlosti v cykle)
            $pricesMat = $db->query("SELECT id_material, cena_MJ FROM MATERIAL")->fetchAll(PDO::FETCH_KEY_PAIR);
            $pricesEdge = $db->query("SELECT id_hrana, cena_bm FROM ABS_HRANA")->fetchAll(PDO::FETCH_KEY_PAIR);

            // 2. Vygenerujeme číslo objednávky (napr. OBJ-20240508-123)
            $cisloObjednavky = "OBJ-" . date('Ymd') . "-" . rand(100, 999);

            // 3. Vložíme hlavičku (podľa vašej schémy)
            $stmt = $db->prepare("INSERT INTO OBJEDNAVKA 
                (nazov_projektu, poznamka, cislo_objednavky, stav, celkova_suma, id_pouzivatel, id_odberatel, celkovy_cas_vyroby_min, created_at) 
                VALUES (?, ?, ?, 'Nová', 0, ?, ?, 0, NOW())");
            $stmt->execute([
                $nazovProjektu,
                $poznamka,
                $cisloObjednavky,
                $user['id_pouzivatel'],
                $user['id_odberatel']
            ]);
            $idObjednavky = $db->lastInsertId();

            // --- ZÁPIS DO TABUĽKY TERMIN (Termínovník) ---
            $terminOd = $_POST['termin_od'] ?? null;
            $terminDo = $_POST['termin_do'] ?? null;

            if ($terminOd) {
                $stmtTermin = $db->prepare("INSERT INTO TERMIN 
                    (typ_terminu, datum_cas_od, datum_cas_do, stav, poznamka, OBJEDNAVKA_id_objednavka, POUZIVATEL_id_pouzivatel) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $stmtTermin->execute([
                    'Výroba',           // typ_terminu (podľa vášho ENUM)
                    $terminOd,
                    $terminDo ?: $terminOd,
                    'Naplánovaný',      // stav (podľa vášho ENUM)
                    'Termín pridaný automaticky pri vytvorení objednávky',
                    $idObjednavky,
                    $user['id_pouzivatel']
                ]);
            }
            $celkovaSumaObjednavky = 0;
            $celkovyCasObjednavky = 0;
            $uploadDir = 'public/uploads/atypy/';


            // 4. Príprava dopytu pre položky (vaša presná schéma)
            $stmtItem = $db->prepare("INSERT INTO POLOZKA_OBJEDNAVKY 
                (nazov_dielu, dlzka_mm, sirka_mm, pocet_kusov, rotacia_textury, cas_vyroby_min, poznamka, priloha_dielu, id_objednavka, id_material, id_horna_hrana, id_dolna_hrana, id_lava_hrana, id_prava_hrana) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($diely as $index => $diel) {
                // A) Súbory
                $cestaKPrilohe = null;
                if (isset($_FILES['diely_prilohy']['name'][$index]) && $_FILES['diely_prilohy']['error'][$index] === UPLOAD_ERR_OK) {
                    $fileName = time() . "_" . $idObjednavky . "_" . basename($_FILES['diely_prilohy']['name'][$index]);
                    move_uploaded_file($_FILES['diely_prilohy']['tmp_name'][$index], $uploadDir . $fileName);
                    $cestaKPrilohe = $uploadDir . $fileName;
                }

                // B) Logika cien a času
                $l = $diel['dlzka'];
                $w = $diel['sirka'];
                $ks = $diel['pocet_kusov'] ?? $diel['kusy'];
                $idMat = $diel['material'];
                $idHrana = !empty($diel['typ_hrany']) ? $diel['typ_hrany'] : null;

                // Matematika dosky
                $plochaM2 = ($l * $w) / 1000000;
                $cenaMat = $pricesMat[$idMat] ?? 0;
                $sumaDosky = $plochaM2 * $cenaMat * $ks;

                // Matematika hrán a času
                $metrazHran = 0;
                $h1 = isset($diel['h1']) ? $idHrana : null;
                $h2 = isset($diel['h2']) ? $idHrana : null;
                $h3 = isset($diel['h3']) ? $idHrana : null;
                $h4 = isset($diel['h4']) ? $idHrana : null;

                if ($h1) $metrazHran += $l;
                if ($h2) $metrazHran += $l;
                if ($h3) $metrazHran += $w;
                if ($h4) $metrazHran += $w;

                $metrazM = $metrazHran / 1000;
                $cenaHran = $pricesEdge[$idHrana] ?? 0;
                $sumaHranTotal = $metrazM * $cenaHran * $ks;

                // Čas výroby (Váš vzorec)
                $casDielu = round((($plochaM2 * 10) + ($metrazM * 0.5)) * $ks, 2);

                $celkovaSumaObjednavky += ($sumaDosky + $sumaHranTotal);
                $celkovyCasObjednavky += $casDielu;

                $stmtItem->execute([
                    $diel['nazov'] ?: 'Dielec',
                    $l,
                    $w,
                    $ks,
                    0,
                    $casDielu,
                    '',
                    $cestaKPrilohe,
                    $idObjednavky,
                    $idMat,
                    $h2,
                    $h1,
                    $h4,
                    $h3
                ]);
            }
            // 4. Po uložení všetkých položiek aktualizujeme hlavičku objednávky s celkovou sumou a časom
            $stmtUpdate = $db->prepare("UPDATE OBJEDNAVKA SET celkova_suma = ?, celkovy_cas_vyroby_min = ? WHERE id_objednavka = ?");
            $stmtUpdate->execute([$celkovaSumaObjednavky, $celkovyCasObjednavky, $idObjednavky]);

            $db->commit();
            header("Location: index.php?page=dashboard&success=1");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            die("Chyba: " . $e->getMessage());
        }
    }

    // 3. Zobrazenie zoznamu objednávok
    public function index()
    {
        $user = $_SESSION['user'];
        $db = Database::connect();

        if ($user['rola'] === 'Admin') {
            // Admin vidí všetko a vidí aj názov firmy, ktorá objednala
            $stmt = $db->query("SELECT o.*, odb.obchodny_nazov 
                            FROM OBJEDNAVKA o 
                            LEFT JOIN ODBERATEL odb ON o.id_odberatel = odb.id_odberatel 
                            ORDER BY o.created_at DESC");
            $orders = $stmt->fetchAll();
        } else {
            // Odberateľ vidí len svoje
            $stmt = $db->prepare("SELECT * FROM OBJEDNAVKA WHERE id_odberatel = ? ORDER BY created_at DESC");
            $stmt->execute([$user['id_odberatel']]);
            $orders = $stmt->fetchAll();
        }

        $view = 'views/orders/index.php';
        require_once 'views/layouts/main.php';
    }

    // 4. Zobrazenie detailu konkrétnej objednávky
    public function show()
    {
        $user = $_SESSION['user'];
        $db = Database::connect();
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            header("Location: index.php?page=orders");
            exit;
        }

        // UPRAVENÉ: Ak je Admin, nefiltrujeme podľa id_pouzivatel / id_odberatel
        if ($user['rola'] === 'Admin') {
            $stmt = $db->prepare("SELECT * FROM OBJEDNAVKA WHERE id_objednavka = ?");
            $stmt->execute([$orderId]);
        } else {
            // Odberateľ vidí len objednávky svojej firmy
            $stmt = $db->prepare("SELECT * FROM OBJEDNAVKA WHERE id_objednavka = ? AND id_odberatel = ?");
            $stmt->execute([$orderId, $user['id_odberatel']]);
        }

        $order = $stmt->fetch();

        if (!$order) {
            die("Objednávka nebola nájdená alebo k nej nemáte prístup.");
        }

        // Načítanie položiek (toto ostáva, ale dajte pozor na presné názvy stĺpcov hrán)
        $stmtItems = $db->prepare("
            SELECT p.*, 
                m.nazov_dekoru as material_nazov,
                m.id_material as material_kod, 
                m.cena_MJ as material_cena,
                h1.nazov as hrana_nazov,
                h1.cena_bm as hrana_cena
            FROM POLOZKA_OBJEDNAVKY p
            LEFT JOIN MATERIAL m ON p.id_material = m.id_material
            LEFT JOIN ABS_HRANA h1 ON p.id_horna_hrana = h1.id_hrana 
            WHERE p.id_objednavka = ?
        ");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        $view = 'views/orders/view.php';
        require_once 'views/layouts/main.php';
    }

    // 5. Aktualizácia stavu objednávky (pre Admina, Obchod, Výroba)
    public function updateStatus()
    {
        $db = Database::connect();
        $orderId = $_POST['id_objednavka'] ?? null;
        $novyStav = $_POST['novy_stav'] ?? null;

        if ($orderId && $novyStav) {
            try {
                $stmt = $db->prepare("UPDATE OBJEDNAVKA SET stav = ? WHERE id_objednavka = ?");
                $stmt->execute([$novyStav, $orderId]);

                header("Location: index.php?page=view_order&id=" . $orderId . "&success=status_updated");
                exit;
            } catch (Exception $e) {
                die("Chyba pri zmene stavu: " . $e->getMessage());
            }
        }
    }

    public function cancelOrder()
    {
        $db = Database::connect();  // Pripojenie k databáze
        $id = $_GET['id'] ?? null;  // ID objednávky, ktorú chceme zrušiť
        $user = $_SESSION['user'];  // Informácie o aktuálnom používateľovi

        if (!$id) {
            header("Location: index.php?page=orders");  // Ak nebolo poskytnuté ID, presmerujeme späť na zoznam objednávok
            exit;
        }

        try {
            // Zabezpečenie: Odberateľ môže zrušiť len svoju, Admin ktorúkoľvek
            $sql = "UPDATE OBJEDNAVKA SET stav = 'Zrušená' WHERE id_objednavka = ?";    // Základný SQL dotaz pro aktualizaci stavu objednávky na 'Zrušená'
            $params = [$id];

            if ($user['rola'] !== 'Admin') {
                $sql .= " AND id_odberatel = ?";
                $params[] = $user['id_odberatel'];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            header("Location: index.php?page=orders&success=cancelled");
        } catch (Exception $e) {
            die("Chyba pri rušení objednávky: " . $e->getMessage());
        }
    }

    // 6. (Voliteľné) Editácia objednávky - Toto je pokročilá funkcia, ktorá by umožnila upravovat objednávku, ale ponecháme ju zatím jako koncept pro budoucí rozšíření
    public function edit()
    {
        $db = Database::connect();
        $id = $_GET['id'];
        $user = $_SESSION['user'];

        // Načítame objednávku
        $stmt = $db->prepare("SELECT * FROM OBJEDNAVKA WHERE id_objednavka = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        // Kontrola: musí byť v stave 'Nová' a patriť používateľovi (ak nie je Admin)
        if (!$order || $order['stav'] !== 'Nová') {
            die("Túto objednávku už nie je možné editovať.");
        }

        if ($user['rola'] !== 'Admin' && $order['id_odberatel'] !== $user['id_odberatel']) {
            die("Nemáte oprávnenie na editáciu tejto objednávky.");
        }

        // Načítame položky, materiály a hrany pre formulár
        $items = $db->prepare("SELECT * FROM POLOZKA_OBJEDNAVKY WHERE id_objednavka = ?");
        $items->execute([$id]);
        $orderItems = $items->fetchAll();

        $materials = $db->query("SELECT * FROM MATERIAL WHERE is_active = 1 ORDER BY nazov_dekoru ASC")->fetchAll();
        $edges = $db->query("SELECT * FROM ABS_HRANA WHERE is_active = 1 ORDER BY nazov ASC")->fetchAll();

        $view = 'views/orders/edit.php'; // Tu vytvoríme podobný formulár ako pri create.php
        require_once 'views/layouts/main.php';
    }

    public function update()
    {
        $db = Database::connect();
        $id_objednavka = $_POST['id_objednavka'];
        $nazov = $_POST['nazov_projektu'];
        $poznamka = $_POST['poznamka'];
        $items = $_POST['items'] ?? [];

        try {
            $db->beginTransaction();

            // 1. Aktualizácia hlavičky
            $stmt = $db->prepare("UPDATE OBJEDNAVKA SET nazov_projektu = ?, poznamka = ? WHERE id_objednavka = ?");
            $stmt->execute([$nazov, $poznamka, $id_objednavka]);

            // 2. Odstránenie starých položiek
            $db->prepare("DELETE FROM POLOZKA_OBJEDNAVKY WHERE id_objednavka = ?")->execute([$id_objednavka]);

            // 3. Vloženie nových/upravených položiek
            foreach ($items as $item) {
                $sql = "INSERT INTO POLOZKA_OBJEDNAVKY (id_objednavka, id_material, dlzka_mm, sirka_mm, pocet_ks) VALUES (?, ?, ?, ?, ?)";
                $db->prepare($sql)->execute([
                    $id_objednavka,
                    $item['id_material'],
                    $item['dlzka_mm'],
                    $item['sirka_mm'],
                    $item['pocet_ks']
                ]);
            }

            // 4. Prepočet celkovej sumy (voliteľné, ale odporúčané)
            // Tu by mala nasledovať vaša kalkulačná logika, ktorú sme robili minule

            $db->commit();
            header("Location: index.php?page=view_order&id=$id_objednavka&success=updated");
        } catch (Exception $e) {
            $db->rollBack();
            die("Chyba pri aktualizácii: " . $e->getMessage());
        }
    }
}
