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
}
