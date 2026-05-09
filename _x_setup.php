<?php
// setup.php - Spustite tento súbor v prehliadači len raz! » Iniciuje databázu a vytvorí prvého admin používateľa.
require_once 'config/database.php';
// Tento skript vytvorí prvého admin používateľa s pevne daným heslom.
try {
    $db = Database::connect();
    
    // Predvolené údaje pre prvého admina
    $meno = 'Hlavný';
    $priezvisko = 'Administrátor';
    $email = 'admin@proma.sk';
    $heslo = 'admin123'; // Toto heslo si potom zmeníte
    $rola = 'Admin';
    
    // Zašifrovanie hesla (Bcrypt)
    $heslo_hash = password_hash($heslo, PASSWORD_DEFAULT);
    
    // Zápis do tabuľky POUZIVATEL
    $stmt = $db->prepare("INSERT INTO POUZIVATEL (meno, priezvisko, email, heslo_hash, rola, is_active, id_odberatel) VALUES (?, ?, ?, ?, ?, 1, NULL)");
    $stmt->execute([$meno, $priezvisko, $email, $heslo_hash, $rola]);
    
    echo "<h1>Úspech!</h1>";
    echo "<p>Admin účet bol vytvorený. E-mail: <strong>$email</strong>, Heslo: <strong>$heslo</strong></p>";
    echo "<p>Teraz tento súbor (setup.php) pre istotu zmažte.</p>";

} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage();
}