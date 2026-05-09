<?php
session_start();


// Pomocná funkcia na kontrolu rolí s presmerovaním
function autorizuj($povoleneRoly)
{
    if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rola'], $povoleneRoly)) {
        header("Location: index.php?page=dashboard&error=unauthorized");
        exit;
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Ochrana: Ak používateľ nie je prihlásený a nesnaží sa práve prihlásiť
if (!isset($_SESSION['user']) && $page !== 'login' && $page !== 'authenticate') {
    header('Location: index.php?page=login');
    exit;
}

// Smerovanie (Router)
switch ($page) {
    case 'login':   // Toto je akcia, ktorá zobrazí prihlasovací formulár
        require_once 'controllers/LoginController.php';
        $controller = new LoginController();
        $controller->index();
        break;

    case 'admin_users': // Toto je akcia, ktorá zobrazí zoznam všetkých používateľov - prístupné len pre Admina
        autorizuj(['Admin']); // Pustí len Admina
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listCustomers();
        break;

    case 'authenticate':    // Toto je akcia, ktorá spracuje prihlasovací formulár
        require_once 'controllers/LoginController.php';
        $controller = new LoginController();
        $controller->authenticate();
        break;

    case 'logout':  // Toto je akcia, ktorá odhlási používateľa
        require_once 'controllers/LoginController.php';
        $controller = new LoginController();
        $controller->logout();
        break;

    case 'admin_customers':
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listCustomers();
        break;

    case 'admin_customer_users':    // Toto je akcia, ktorá zobrazí používateľov konkrétnej firmy - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listCustomerUsers();
        break;

    case 'save_customer_user':  // Toto je akcia, ktorá spracuje formulár pro pridanie nového používateľa do firmy - prístupné len pro Admina   
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->saveCustomerUser();
        break;

    case 'save_customer':   // Toto je akcia, ktorá spracuje formulár pre pridanie novej firmy a uloží ju do databázy - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->saveCustomer();
        break;

    case 'admin_materials':  // Toto je akcia, ktorá zobrazí zoznam materiálov - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listMaterials();
        break;

    case 'save_material':   // Toto je akcia, ktorá spracuje formulár pro pridanie nového materiálu a uloží ho do databázy - prístupné len pro Admina
        autorizuj(['Admin']);
        autorizuj(['Admin', 'Odberatel']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->saveMaterial();
        break;

    case 'update_material':     // Toto je akcia, která spracuje formulár pro úpravu materiálu a uloží změny do databázy - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateMaterial();
        break;

    case 'dashboard':   // Toto je nástenka, ktorú vidí používateľ po prihlásení
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'new_order':   // Toto je akcia, ktorá zobrazí formulár pre vytvorenie novej objednávky
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->create();
        break;

    case 'save_order': // Toto je akcia, ktorá spracuje formulár novej objednávky a uloží ju do databázy
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->store();
        break;

    case 'orders':  // Toto je akcia, ktorá zobrazí zoznam všetkých objednávok
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->index();
        break;

    case 'view_order': // Toto je akcia, ktorá zobrazí detail konkrétnej objednávky
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->show();
        break;

    case 'update_order_status':     // Toto je akcia, která umožní aktualizovat stav objednávky - Odberateľ může aktualizovat pouze své objednávky, Admin může aktualizovat jakoukoliv objednávku
        autorizuj(['Admin', 'Obchod', 'Vyroba']);
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->updateStatus();
        break;

    case 'cancel_order':    // Toto je akcia, která umožní zrušit objednávku - Odberateľ může zrušit pouze své objednávky, Admin může zrušit jakoukoliv objednávku
        autorizuj(['Admin', 'Odberatel']);
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->cancelOrder();
        break;

    case 'edit_order':
        autorizuj(['Admin', 'Odberatel']);
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->edit(); // Metódu edit() sme si pripravili v predchádzajúcom kroku
        break;

    case 'update_order':
        autorizuj(['Admin', 'Odberatel']);
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->update();
        break;

    default:
        // Tu to spadne, ak kliknete na niečo, čo ešte neexistuje (napr. new_order)
        echo "<h1>404 - Stránka nenájdená</h1>";
        echo "<p>Hľadaná stránka: " . htmlspecialchars($page) . " zatiaľ neexistuje.</p>";
        echo "<a href='index.php?page=dashboard'>Späť na nástenku</a>";
        break;
}
