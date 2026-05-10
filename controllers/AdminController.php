<?php
class AdminController
{

    public function listCustomers()
    {
        $db = Database::connect();

        // Načítame všetky firmy
        $stmt = $db->query("SELECT * FROM ODBERATEL ORDER BY obchodny_nazov ASC");
        $customers = $stmt->fetchAll();

        $view = 'views/admin/customers.php';
        require_once 'views/layouts/main.php';
    }

    public function saveCustomer()
    {
        $db = Database::connect();

        // Načítanie všetkých údajov z formulára (s fallbackom na prázdny reťazec, ak nie sú vyplnené)
        $nazov = $_POST['obchodny_nazov'] ?? '';
        $ico = $_POST['ico'] ?? '';
        $dic = $_POST['dic'] ?? '';
        $ic_dph = $_POST['ic_dph'] ?? '';
        $ulica = $_POST['ulica'] ?? '';
        $mesto = $_POST['mesto'] ?? '';
        $psc = $_POST['psc'] ?? '';
        $stat = $_POST['stat'] ?? 'Slovensko'; // Predvolená hodnota
        $telefon = $_POST['telefon'] ?? '';
        $is_active = 1; // Nová firma je automaticky aktívna

        try {
            // Príprava SQL dopytu so všetkými atribútmi (okrem id_odberatel, to sa generuje samo)
            $stmt = $db->prepare("INSERT INTO ODBERATEL 
                (obchodny_nazov, ico, dic, ic_dph, ulica, mesto, psc, stat, telefon, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Spustenie dopytu
            $stmt->execute([
                $nazov,
                $ico,
                $dic,
                $ic_dph,
                $ulica,
                $mesto,
                $psc,
                $stat,
                $telefon,
                $is_active
            ]);

            // Presmerovanie späť na zoznam firiem
            header("Location: index.php?page=admin_customers&success=customer_added");
            exit;
        } catch (Exception $e) {
            die("Chyba pri ukladaní firmy: " . $e->getMessage());
        }
    }
    // Pridajte do controllers/AdminController.php

    public function listCustomerUsers()
    {
        $customerId = $_GET['id_odberatel'] ?? null;
        if (!$customerId) {
            header("Location: index.php?page=admin_customers");
            exit;
        }

        $db = Database::connect();

        // Získame info o firme
        $stmtCust = $db->prepare("SELECT * FROM ODBERATEL WHERE id_odberatel = ?");
        $stmtCust->execute([$customerId]);
        $customer = $stmtCust->fetch();

        if (!$customer) {
            die("Firma neexistuje.");
        }

        // Získame používateľov priradených k tejto firme
        $stmtUsers = $db->prepare("SELECT * FROM POUZIVATEL WHERE id_odberatel = ? ORDER BY priezvisko ASC");
        $stmtUsers->execute([$customerId]);
        $users = $stmtUsers->fetchAll();

        $view = 'views/admin/customer_users.php';
        require_once 'views/layouts/main.php';
    }

    public function saveCustomerUser()
    {
        $db = Database::connect();

        $id_odberatel = $_POST['id_odberatel'];
        $meno = $_POST['meno'];
        $priezvisko = $_POST['priezvisko'];
        $email = $_POST['email'];
        $heslo = $_POST['heslo'];
        $rola = 'Odberatel'; // Fixne priradená rola pre externého partnera

        // Šifrovanie hesla podľa moderných štandardov
        $heslo_hash = password_hash($heslo, PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO POUZIVATEL 
                (meno, priezvisko, email, heslo_hash, rola, is_active, id_odberatel) 
                VALUES (?, ?, ?, ?, ?, 1, ?)");
            $stmt->execute([$meno, $priezvisko, $email, $heslo_hash, $rola, $id_odberatel]);

            header("Location: index.php?page=admin_customer_users&id_odberatel=$id_odberatel&success=user_added");
            exit;
        } catch (Exception $e) {
            die("Chyba pri vytváraní používateľa: " . $e->getMessage());
        }
    }

    // --- MATERIÁLY ---
    public function listMaterials()
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM MATERIAL ORDER BY nazov_dekoru ASC");
        $materials = $stmt->fetchAll();

        $view = 'views/admin/materials.php';
        require_once 'views/layouts/main.php';
    }

    public function saveMaterial()
    {
        $db = Database::connect();

        // 1. Načítame dáta (všimnite si, že id_material tu už nie je!)
        $nazov = $_POST['nazov_dekoru'] ?? '';
        $kod_vyrobcu = $_POST['kod_vyrobcu'] ?? '';
        $typ = $_POST['typ'] ?? 'LDTD';
        $hrubka = $_POST['hrubka_mm'] ?? 18.0;
        $dlzka = $_POST['dlzka_mm'] ?? 2800;
        $sirka = $_POST['sirka_mm'] ?? 2070;
        $mj = $_POST['mj'] ?? 'm2';
        $obrazok = $_POST['obrazok_url'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Ošetrenie ceny - nahradíme čiarku bodkou, aby to SQL zobral ako číslo
        $cena = str_replace(',', '.', $_POST['cena_MJ'] ?? '0');

        try {
            // 2. SQL INSERT - presne 10 stĺpcov (id_material vynechávame, je AUTO_INCREMENT)
            $sql = "INSERT INTO MATERIAL 
                    (nazov_dekoru, kod_vyrobcu, typ, hrubka_mm, dlzka_mm, sirka_mm, cena_MJ, mj, obrazok_url, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);

            // 3. Execute - pole musí mať presne 10 hodnôt v rovnakom poradí ako v SQL
            $stmt->execute([
                $nazov,         // 1
                $kod_vyrobcu,   // 2
                $typ,           // 3
                $hrubka,        // 4
                $dlzka,         // 5
                $sirka,         // 6
                $cena,          // 7
                $mj,            // 8
                $obrazok,       // 9
                $is_active      // 10
            ]);

            header("Location: index.php?page=admin_materials&success=added");
            exit;
        } catch (Exception $e) {
            // Ak vypíše chybu, uvidíme presne kde je problém
            die("Chyba pri ukladaní: " . $e->getMessage());
        }
    }

    public function updateMaterial()
    {
        $db = Database::connect();

        $id_material = $_POST['id_material'] ?? null;
        $nazov = $_POST['nazov_dekoru'] ?? '';
        $kod_vyrobcu = $_POST['kod_vyrobcu'] ?? '';
        $typ = $_POST['typ'] ?? 'LDTD';
        $hrubka = $_POST['hrubka_mm'] ?? 18.0;
        $dlzka = $_POST['dlzka_mm'] ?? 2800;
        $sirka = $_POST['sirka_mm'] ?? 2070;
        $mj = $_POST['mj'] ?? 'm2';
        $obrazok = $_POST['obrazok_url'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $cena = str_replace(',', '.', $_POST['cena_MJ'] ?? '0');

        if (!$id_material) die("Chýba ID materiálu.");

        try {
            $sql = "UPDATE MATERIAL SET 
                nazov_dekoru = ?, kod_vyrobcu = ?, typ = ?, hrubka_mm = ?, 
                dlzka_mm = ?, sirka_mm = ?, cena_MJ = ?, mj = ?, 
                obrazok_url = ?, is_active = ? 
                WHERE id_material = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $nazov,
                $kod_vyrobcu,
                $typ,
                $hrubka,
                $dlzka,
                $sirka,
                $cena,
                $mj,
                $obrazok,
                $is_active,
                $id_material
            ]);

            header("Location: index.php?page=admin_materials&success=updated");
            exit;
        } catch (Exception $e) {
            die("Chyba pri aktualizácii: " . $e->getMessage());
        }
    }

    public function listStaff()
    {
        $db = Database::connect();
        // Vyberieme len interných zamestnancov (kde id_odberatel je NULL)
        $stmt = $db->query("SELECT * FROM POUZIVATEL WHERE id_odberatel IS NULL ORDER BY priezvisko ASC");
        $staff = $stmt->fetchAll();

        $view = 'views/admin/staff.php';
        require_once 'views/layouts/main.php';
    }

    public function saveStaff()
    {
        $db = Database::connect();
        $meno = $_POST['meno'];
        $priezvisko = $_POST['priezvisko'];
        $email = $_POST['email'];
        $rola = $_POST['rola'];
        $heslo_hash = password_hash($_POST['heslo'], PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO POUZIVATEL (meno, priezvisko, email, heslo_hash, rola, is_active, id_odberatel) VALUES (?, ?, ?, ?, ?, 1, NULL)");
            $stmt->execute([$meno, $priezvisko, $email, $heslo_hash, $rola]);
            header("Location: index.php?page=admin_staff&success=1");
        } catch (Exception $e) {
            die("Chyba: " . $e->getMessage());
        }
    }

    public function updateStaff()
    {
        $db = Database::connect();

        $id = $_POST['id_pouzivatel'];
        $meno = $_POST['meno'];
        $priezvisko = $_POST['priezvisko'];
        $email = $_POST['email'];
        $rola = $_POST['rola'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $nove_heslo = $_POST['heslo'] ?? '';

        try {
            // Základný UPDATE
            $sql = "UPDATE POUZIVATEL SET meno = ?, priezvisko = ?, email = ?, rola = ?, is_active = ? WHERE id_pouzivatel = ?";
            $params = [$meno, $priezvisko, $email, $rola, $is_active, $id];

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // Ak bolo zadané nové heslo, zmeníme aj to
            if (!empty($nove_heslo)) {
                $hash = password_hash($nove_heslo, PASSWORD_DEFAULT);
                $db->prepare("UPDATE POUZIVATEL SET heslo_hash = ? WHERE id_pouzivatel = ?")->execute([$hash, $id]);
            }

            header("Location: index.php?page=admin_staff&success=updated");
            exit;
        } catch (Exception $e) {
            die("Chyba pri aktualizácii zamestnanca: " . $e->getMessage());
        }
    }

    // --- VOZIDLÁ ---
    public function listVehicles()
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM VOZIDLO ORDER BY spz ASC");
        $vehicles = $stmt->fetchAll();

        $view = 'views/admin/vehicles.php';
        require_once 'views/layouts/main.php';
    }

    public function saveVehicle()
    {
        $db = Database::connect();
        
        $spz = $_POST['spz'] ?? '';
        $znacka_model = $_POST['znacka_model'] ?? '';
        $nosnost_kg = $_POST['nosnost_kg'] ?? 0;
        $objem_m3 = str_replace(',', '.', $_POST['objem_m3'] ?? '0');
        $stav = $_POST['stav'] ?? 'Dostupné';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $db->prepare("INSERT INTO VOZIDLO (spz, znacka_model, nosnost_kg, objem_m3, stav, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$spz, $znacka_model, $nosnost_kg, $objem_m3, $stav, $is_active]);
            header("Location: index.php?page=admin_vehicles&success=added");
            exit;
        } catch (Exception $e) {
            die("Chyba pri ukladaní vozidla (Možno duplicitná ŠPZ?): " . $e->getMessage());
        }
    }

    public function updateVehicle()
    {
        $db = Database::connect();
        
        $id_vozidlo = $_POST['id_vozidlo'] ?? null;
        $spz = $_POST['spz'] ?? '';
        $znacka_model = $_POST['znacka_model'] ?? '';
        $nosnost_kg = $_POST['nosnost_kg'] ?? 0;
        $objem_m3 = str_replace(',', '.', $_POST['objem_m3'] ?? '0');
        $stav = $_POST['stav'] ?? 'Dostupné';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$id_vozidlo) die("Chýba ID vozidla.");

        try {
            $stmt = $db->prepare("UPDATE VOZIDLO SET spz = ?, znacka_model = ?, nosnost_kg = ?, objem_m3 = ?, stav = ?, is_active = ? WHERE id_vozidlo = ?");
            $stmt->execute([$spz, $znacka_model, $nosnost_kg, $objem_m3, $stav, $is_active, $id_vozidlo]);
            header("Location: index.php?page=admin_vehicles&success=updated");
            exit;
        } catch (Exception $e) {
            die("Chyba pri aktualizácii vozidla: " . $e->getMessage());
        }
    }

    // --- HRANY (ABS) ---
    public function listEdges()
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM ABS_HRANA ORDER BY nazov ASC");
        $edges = $stmt->fetchAll();

        $view = 'views/admin/edges.php';
        require_once 'views/layouts/main.php';
    }

    public function saveEdge()
    {
        $db = Database::connect();
        
        $nazov = $_POST['nazov'] ?? '';
        $kod_vyrobcu = $_POST['kod_vyrobcu'] ?? '';
        $hrubka_mm = $_POST['hrubka_mm'] ?? 0;
        $sirka_mm = $_POST['sirka_mm'] ?? 0;
        $cena_bm = str_replace(',', '.', $_POST['cena_bm'] ?? '0');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $db->prepare("INSERT INTO ABS_HRANA (nazov, kod_vyrobcu, hrubka_mm, sirka_mm, cena_bm, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nazov, $kod_vyrobcu, $hrubka_mm, $sirka_mm, $cena_bm, $is_active]);
            header("Location: index.php?page=admin_edges&success=added");
            exit;
        } catch (Exception $e) {
            die("Chyba pri ukladaní hrany: " . $e->getMessage());
        }
    }

    public function updateEdge()
    {
        $db = Database::connect();
        
        $id_hrana = $_POST['id_hrana'] ?? null;
        $nazov = $_POST['nazov'] ?? '';
        $kod_vyrobcu = $_POST['kod_vyrobcu'] ?? '';
        $hrubka_mm = $_POST['hrubka_mm'] ?? 0;
        $sirka_mm = $_POST['sirka_mm'] ?? 0;
        $cena_bm = str_replace(',', '.', $_POST['cena_bm'] ?? '0');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$id_hrana) die("Chýba ID hrany.");

        try {
            $stmt = $db->prepare("UPDATE ABS_HRANA SET nazov = ?, kod_vyrobcu = ?, hrubka_mm = ?, sirka_mm = ?, cena_bm = ?, is_active = ? WHERE id_hrana = ?");
            $stmt->execute([$nazov, $kod_vyrobcu, $hrubka_mm, $sirka_mm, $cena_bm, $is_active, $id_hrana]);
            header("Location: index.php?page=admin_edges&success=updated");
            exit;
        } catch (Exception $e) {
            die("Chyba pri aktualizácii hrany: " . $e->getMessage());
        }
    }
}
