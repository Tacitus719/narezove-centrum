<?php
class ComplaintController {

    public function index() {
        $db = Database::connect();
        $user = $_SESSION['user'];
        
        $isStaff = in_array($user['rola'], ['Admin', 'Vyroba', 'Obchod']);
        
        // Ak je to personál, vidí všetko. Ak odberateľ, vidí len svoje.
        if ($isStaff) {
            $sql = "SELECT r.*, o.cislo_objednavky, odb.obchodny_nazov, u.meno, u.priezvisko 
                    FROM REKLAMACIA r
                    JOIN OBJEDNAVKA o ON r.id_objednavka = o.id_objednavka
                    JOIN POUZIVATEL u ON r.id_nahlasovatel = u.id_pouzivatel
                    LEFT JOIN ODBERATEL odb ON o.id_odberatel = odb.id_odberatel
                    ORDER BY r.datum_podania DESC";
            $stmt = $db->query($sql);
        } else {
            $sql = "SELECT r.*, o.cislo_objednavky, u.meno, u.priezvisko 
                    FROM REKLAMACIA r
                    JOIN OBJEDNAVKA o ON r.id_objednavka = o.id_objednavka
                    JOIN POUZIVATEL u ON r.id_nahlasovatel = u.id_pouzivatel
                    WHERE o.id_odberatel = ?
                    ORDER BY r.datum_podania DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['id_odberatel']]);
        }
        
        $complaints = $stmt->fetchAll();
        
        $view = 'views/complaints/index.php';
        require_once 'views/layouts/main.php';
    }
    
    public function create() {
        $db = Database::connect();
        $orderId = $_GET['order_id'];
        $user = $_SESSION['user'];

        // Načítame objednávku a jej položky
        $stmt = $db->prepare("SELECT * FROM OBJEDNAVKA WHERE id_objednavka = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        $stmtItems = $db->prepare("SELECT * FROM POLOZKA_OBJEDNAVKY WHERE id_objednavka = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        $view = 'views/complaints/create.php';
        require_once 'views/layouts/main.php';
    }

    public function show() {
        $db = Database::connect();
        $id = $_GET['id'];
        $user = $_SESSION['user'];

        // Načítanie hlavičky reklamácie s informáciami o objednávke a nahlasovateľovi
        $stmt = $db->prepare("SELECT r.*, o.cislo_objednavky, o.id_odberatel, u.meno, u.priezvisko
                              FROM REKLAMACIA r
                              JOIN OBJEDNAVKA o ON r.id_objednavka = o.id_objednavka
                              JOIN POUZIVATEL u ON r.id_nahlasovatel = u.id_pouzivatel
                              WHERE r.id_reklamacia = ?");
        $stmt->execute([$id]);
        $complaint = $stmt->fetch();

        if (!$complaint) die("Reklamácia nenájdená.");

        // Ochrana súkromia: Odberateľ vidí len svoje
        if (!in_array($user['rola'], ['Admin', 'Vyroba', 'Obchod']) && $complaint['id_odberatel'] != $user['id_odberatel']) {
            die("Nemáte povolenie na zobrazenie tejto reklamácie.");
        }

        // Načítanie položiek reklamácie vrátane počtu kusov a rozmerov z pôvodnej objednávky
        $stmtItems = $db->prepare("SELECT pr.*, po.nazov_dielu, po.dlzka_mm, po.sirka_mm, po.pocet_kusov as povodny_pocet
                                   FROM POLOZKA_REKLAMACIE pr
                                   JOIN POLOZKA_OBJEDNAVKY po ON pr.id_polozka_objednavky = po.id_polozka
                                   WHERE pr.id_reklamacia = ?");
        $stmtItems->execute([$id]);
        $items = $stmtItems->fetchAll();

        $view = 'views/complaints/view.php';
        require_once 'views/layouts/main.php';
    }

    public function updateStatus() {
        $db = Database::connect();
        $user = $_SESSION['user'];
        
        $idReklamacia = $_POST['id_reklamacia'];
        $novyStav = $_POST['stav'];
        $vyjadrenie = $_POST['vyjadrenie_obchodu'] ?? null;

        try {
            // Pri uzavretí (Vybavená/Zamietnutá) nastavíme aj dátum ukončenia
            $datumUkoncenia = in_array($novyStav, ['Vybavená', 'Zamietnutá']) ? ", datum_ukoncenia = NOW()" : "";
            
            $sql = "UPDATE REKLAMACIA SET stav = ?, vyjadrenie_obchodu = ?, id_schvalovatel = ? $datumUkoncenia WHERE id_reklamacia = ?";
            $db->prepare($sql)->execute([$novyStav, $vyjadrenie, $user['id_pouzivatel'], $idReklamacia]);
            
            header("Location: index.php?page=view_complaint&id=$idReklamacia&success=updated");
        } catch (Exception $e) {
            die("Chyba: " . $e->getMessage());
        }
    }

    public function store() {
        $db = Database::connect();
        $user = $_SESSION['user'];
        
        $orderId = $_POST['id_objednavka'];
        $reklamovaneDiely = $_POST['reklamovane_diely'] ?? [];

        try {
            $db->beginTransaction();

            // 1. Uloženie hlavičky reklamácie
            $stmt = $db->prepare("INSERT INTO REKLAMACIA (stav, id_objednavka, id_nahlasovatel) VALUES (?, ?, ?)");
            $stmt->execute(['Prijatá', $orderId, $user['id_pouzivatel']]);
            $reklamaciaId = $db->lastInsertId();

            // 2. Uloženie jednotlivých položiek (pridali sme pocet_kusov)
            $stmtItem = $db->prepare("INSERT INTO POLOZKA_REKLAMACIE (popis_vady, id_reklamacia, id_polozka_objednavky, pocet_kusov) VALUES (?, ?, ?, ?)");
            
            foreach ($reklamovaneDiely as $polozkaId => $data) {
                if (!empty($data['vybrate'])) {
                    // Ak kusy nie sú zadané, predvolene berieme 1
                    $kusy = isset($data['kusy']) ? (int)$data['kusy'] : 1;
                    
                    $stmtItem->execute([
                        $data['popis'],
                        $reklamaciaId,
                        $polozkaId,
                        $kusy
                    ]);
                }
            }

            $db->commit();
            header("Location: index.php?page=view_order&id=$orderId&success=reklamacia_odoslana");
        } catch (Exception $e) {
            $db->rollBack();
            die("Chyba: " . $e->getMessage());
        }
    }
}