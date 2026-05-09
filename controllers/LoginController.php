<?php
// controllers/LoginController.php

class LoginController {
    
    public function index() {
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['error']); 
        require_once 'views/auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $db = Database::connect();
            
            // Hľadáme používateľa podľa emailu
            $stmt = $db->prepare("SELECT * FROM POUZIVATEL WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['heslo_hash'])) {
        // Prihlásenie úspešné! Uložíme do SESSION všetky dôležité RBAC dáta
        $_SESSION['user'] = [
            'id_pouzivatel' => $user['id_pouzivatel'],
            'meno' => $user['meno'],
            'priezvisko' => $user['priezvisko'],
            'rola' => $user['rola'], // Kľúčové pre oprávnenia
            'id_odberatel' => $user['id_odberatel'] // Ak je NULL, je to interný zamestnanec
        ];
        
        // Presmerovanie po prihlásení
        header("Location: index.php?page=dashboard");
        exit;
        
    } else {
        // Zlé heslo
        $_SESSION['error'] = "Nesprávný email nebo heslo.";
        header("Location: index.php?page=login");
        exit;   

    }

            // Ak nič nesedí
            $_SESSION['error'] = "Nesprávny email alebo heslo.";
            header("Location: index.php?page=login");
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}