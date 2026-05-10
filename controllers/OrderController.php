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
            // 5. Uloženie do termínovníka
            $terminOdberu = $_POST['termin_odberu'] ?? date('Y-m-d', strtotime('+3 days'));
            
            // Vygenerujeme presné časy pre databázu (od rána do poobedia)
            $datumOd = $terminOdberu . ' 08:00:00';
            $datumDo = $terminOdberu . ' 16:00:00';

            // Doplnili sme datum_cas_do do SQL príkazu
            $stmtTermin = $db->prepare("INSERT INTO TERMIN (typ_terminu, datum_cas_od, datum_cas_do, stav, OBJEDNAVKA_id_objednavka, POUZIVATEL_id_pouzivatel) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtTermin->execute([
                'Výroba', 
                $datumOd, 
                $datumDo, 
                'Naplánovaný', 
                $idObjednavky, 
                $user['id_pouzivatel']
            ]);

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

        // 1. Definujeme si, kto je interný personál
        $isStaff = in_array($user['rola'], ['Admin', 'Vyroba', 'Obchod', 'Logistika', 'Doprava']);

        if ($isStaff) {
            // ZÁKLADNÝ SQL PRE PERSONÁL
            $sql = "SELECT o.*, odb.obchodny_nazov, v.znacka_model, v.spz 
                    FROM OBJEDNAVKA o 
                    LEFT JOIN ODBERATEL odb ON o.id_odberatel = odb.id_odberatel
                    LEFT JOIN VOZIDLO v ON o.id_vozidlo = v.id_vozidlo";

            // ŠPECIÁLNY FILTER PRE LOGISTIKU A DOPRAVU
            if (in_array($user['rola'], ['Logistika', 'Doprava'])) {
                $sql .= " WHERE o.stav IN ('Vo výrobe', 'Vyrobená', 'Expedovaná')";
            }

            $sql .= " ORDER BY o.created_at DESC";
            $stmt = $db->query($sql);
            $orders = $stmt->fetchAll();
        } else {
            // SQL PRE ODBERATEĽA (vidí len svoje)
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

        // UPRAVENÉ: Ak ide o zamestnanca firmy (Admin, Výroba, Doprava atď.), má prístup k zobrazeniu všetkých objednávok
        if (in_array($user['rola'], ['Admin', 'Vyroba', 'Obchod', 'Logistika', 'Doprava'])) {
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

        $vozidla = $db->query("SELECT * FROM VOZIDLO WHERE is_active = 1")->fetchAll();
        
        $view = 'views/orders/view.php';
        require_once 'views/layouts/main.php';
    }

    // 5. Aktualizácia stavu objednávky (pre Admina, Obchod, Výroba)
public function updateStatus() {
    $db = Database::connect();
    $id_obj = $_POST['id_objednavka'];
    $novyStavObj = $_POST['novy_stav'];
    $id_vozidlo = $_POST['id_vozidlo'] ?? null;
    $pouzivatel_id = $_SESSION['user']['id_pouzivatel'];

    try {
        $db->beginTransaction();

        // 1. Aktualizácia stavu OBJEDNÁVKY a priradenie vozidla (ak je poslané)
        $sqlObj = "UPDATE OBJEDNAVKA SET stav = ?";
        $paramsObj = [$novyStavObj];
        
        if ($id_vozidlo) {
            $sqlObj .= ", id_vozidlo = ?";
            $paramsObj[] = $id_vozidlo;
        }
        
        $sqlObj .= " WHERE id_objednavka = ?";
        $paramsObj[] = $id_obj;
        $db->prepare($sqlObj)->execute($paramsObj);

        // 2. Logika pre TERMIN na základe stavu objednávky
        $novyStavTermin = null;
        
        switch ($novyStavObj) {
            case 'Vo výrobe':
                $novyStavTermin = 'Prebieha';
                break;
            case 'Vyrobená':
                $novyStavTermin = 'Na vývoz'; // Objednávka ostáva aktívna pre logistiku
                break;
            case 'Expedovaná':
                $novyStavTermin = 'Dokončený'; // Definitívne vybavené
                break;
            case 'Zrušená':
                $novyStavTermin = 'Zrušený';
                break;
        }

        if ($novyStavTermin) {
            $db->prepare("UPDATE TERMIN SET stav = ? WHERE OBJEDNAVKA_id_objednavka = ?")
               ->execute([$novyStavTermin, $id_obj]);
        }

        $db->commit();
        header("Location: index.php?page=view_order&id=$id_obj&success=1");
    } catch (Exception $e) {
        $db->rollBack();
        die("Chyba synchronizácie: " . $e->getMessage());
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
    public function edit() {
        $db = Database::connect();
        $id = $_GET['id'];
        $user = $_SESSION['user'];

        $stmt = $db->prepare("SELECT * FROM OBJEDNAVKA WHERE id_objednavka = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order || $order['stav'] !== 'Nová') {
            die("Túto objednávku nie je možné upraviť.");
        }

        // NAČÍTANIE POLOŽIEK - uistite sa, že názvy stĺpcov sú presné (id_horna_hrana, atď.)
        $stmtItems = $db->prepare("SELECT * FROM POLOZKA_OBJEDNAVKY WHERE id_objednavka = ?");
        $stmtItems->execute([$id]);
        $orderItems = $stmtItems->fetchAll();

        // Načítanie materiálov a hrán pre selecty
        $materials = $db->query("SELECT * FROM MATERIAL WHERE is_active = 1 ORDER BY nazov_dekoru ASC")->fetchAll();
        $edges = $db->query("SELECT * FROM ABS_HRANA WHERE is_active = 1 ORDER BY nazov ASC")->fetchAll();

        $view = 'views/orders/edit.php';
        require_once 'views/layouts/main.php';
    }
    // 7. (Voliteľné) Uloženie upravenej objednávky - Toto by byla logika pro aktualizaci objednávky po editaci, včetně přepočítání ceny a času
    public function update()
        {
            $db = Database::connect();
            $idObjednavky = $_POST['id_objednavka'];
            $nazovProjektu = $_POST['nazov_projektu'];
            $poznamka = $_POST['poznamka'] ?? '';
            $diely = $_POST['diely'] ?? [];

            try {
                $db->beginTransaction();

                // Ceny do pamäte pre rýchly prepočet
                $pricesMat = $db->query("SELECT id_material, cena_MJ FROM MATERIAL")->fetchAll(PDO::FETCH_KEY_PAIR);
                $pricesEdge = $db->query("SELECT id_hrana, cena_bm FROM ABS_HRANA")->fetchAll(PDO::FETCH_KEY_PAIR);

                // 1. Aktualizácia hlavičky
                $stmt = $db->prepare("UPDATE OBJEDNAVKA SET nazov_projektu = ?, poznamka = ? WHERE id_objednavka = ?");
                $stmt->execute([$nazovProjektu, $poznamka, $idObjednavky]);

                // 2. Vymazanie starých položiek (je to najistejší spôsob, ako urobiť čistý update)
                $db->prepare("DELETE FROM POLOZKA_OBJEDNAVKY WHERE id_objednavka = ?")->execute([$idObjednavky]);

                $celkovaSumaObjednavky = 0;
                $celkovyCasObjednavky = 0;

                // 3. Vloženie nanovo (z upraveného poľa)
                $stmtItem = $db->prepare("INSERT INTO POLOZKA_OBJEDNAVKY 
                    (nazov_dielu, dlzka_mm, sirka_mm, pocet_kusov, rotacia_textury, cas_vyroby_min, poznamka, priloha_dielu, id_objednavka, id_material, id_horna_hrana, id_dolna_hrana, id_lava_hrana, id_prava_hrana) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($diely as $index => $diel) {
                    $l = $diel['dlzka'];
                    $w = $diel['sirka'];
                    $ks = $diel['kusy'] ?? 1;
                    $idMat = $diel['material'];
                    $idHrana = !empty($diel['typ_hrany']) ? $diel['typ_hrany'] : null;

                    // Prepočty (rovnaké ako pri tvorbe objednávky)
                    $plochaM2 = ($l * $w) / 1000000;
                    $cenaMat = $pricesMat[$idMat] ?? 0;
                    $sumaDosky = $plochaM2 * $cenaMat * $ks;

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
                        null, // Pri update zatiaľ vynechávame znovu-nahrávanie súborov kvôli zložitosti
                        $idObjednavky,
                        $idMat,
                        $h2,
                        $h1,
                        $h4,
                        $h3
                    ]);
                }

                // 4. Prepočet celkovej ceny na hlavičke objednávky
                $stmtUpdate = $db->prepare("UPDATE OBJEDNAVKA SET celkova_suma = ?, celkovy_cas_vyroby_min = ? WHERE id_objednavka = ?");
                $stmtUpdate->execute([$celkovaSumaObjednavky, $celkovyCasObjednavky, $idObjednavky]);

                $db->commit();
                header("Location: index.php?page=view_order&id=$idObjednavky&success=updated");
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                die("Chyba pri aktualizácii: " . $e->getMessage());
            }
        }
                // --- MANAŽMENT TERMÍNOV ---

    public function addTermin() {
        $db = Database::connect();
        $user = $_SESSION['user'];

        $idObjednavky = $_POST['id_objednavka'];
        $typTerminu = $_POST['typ_terminu'];
        
        // Ak si zvolili "Iné", použijeme ich vlastný text
        if ($typTerminu === 'Iné...') {
            $typTerminu = $_POST['typ_terminu_vlastne'] ?: 'Nezaradený termín';
        }

        $datum = $_POST['datum'];
        $cas = $_POST['cas'] ?: '12:00'; // Predvolený čas, ak nezadajú
        
        $datumCasOd = $datum . ' ' . $cas . ':00';
        $datumCasDo = $datumCasOd; // Zatiaľ si to zjednodušíme a "do" dáme rovnako

        try {
            $stmt = $db->prepare("INSERT INTO TERMIN (typ_terminu, datum_cas_od, datum_cas_do, stav, OBJEDNAVKA_id_objednavka, POUZIVATEL_id_pouzivatel) VALUES (?, ?, ?, 'Naplánovaný', ?, ?)");
            $stmt->execute([$typTerminu, $datumCasOd, $datumCasDo, $idObjednavky, $user['id_pouzivatel']]);
            header("Location: index.php?page=view_order&id=$idObjednavky&success=termin_pridany");
        } catch (Exception $e) {
            die("Chyba pri pridávaní termínu: " . $e->getMessage());
        }
    }

    public function completeTermin() {
        $db = Database::connect();
        $idTermin = $_GET['id_termin'];
        $idObjednavky = $_GET['id_objednavka'];
        
        $stmt = $db->prepare("UPDATE TERMIN SET stav = 'Dokončený' WHERE id_termin = ?");
        $stmt->execute([$idTermin]);
        header("Location: index.php?page=view_order&id=$idObjednavky&success=termin_dokonceny");
    }

    public function deleteTermin() {
        $db = Database::connect();
        $idTermin = $_GET['id_termin'];
        $idObjednavky = $_GET['id_objednavka'];
        
        $stmt = $db->prepare("DELETE FROM TERMIN WHERE id_termin = ?");
        $stmt->execute([$idTermin]);
        header("Location: index.php?page=view_order&id=$idObjednavky&success=termin_zmazany");
    }
}
