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
        
    case 'admin_staff': // Toto je akcia, která zobrazí zoznam všetkých zamestnancov - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listStaff();
        break;

    case 'save_staff': // Toto je akcia, ktorá spracuje formulár pre pridanie nového zamestnanca - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->saveStaff();
        break;

    case 'update_staff':    // Toto je akcia, která spracuje formulár pro úpravu zamestnance a uloží změny do databázy - prístupné len pro Admina
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateStaff();
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

    case 'admin_vehicles': // Výpis vozidiel
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listVehicles();
        break;

    case 'save_vehicle': // Uloženie nového vozidla
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->saveVehicle();
        break;

    case 'update_vehicle': // Úprava vozidla
        autorizuj(['Admin']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateVehicle();
        break;

    case 'admin_edges': // Výpis ABS hrán
        autorizuj(['Admin', 'Obchod']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->listEdges();
        break;

    case 'save_edge': // Uloženie novej hrany
        autorizuj(['Admin', 'Obchod']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->saveEdge();
        break;

    case 'update_edge': // Úprava existujúcej hrany
        autorizuj(['Admin', 'Obchod']);
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateEdge();
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

    case 'update_order_status':     
        autorizuj(['Admin', 'Obchod', 'Vyroba', 'Logistika', 'Doprava']); // Toto je akcia, která umožní změnit stav objednávky - přístupné pro Admina, Obchod, Výrobu, Logistiku a Dopravu (Odberatel to nepotrebuje vidět)
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
    
    case 'complaints':  // Toto je akcia, která zobrazí zoznam reklamácií - přístupné pro Admina, Obchod a Výrobu (Logistika to nepotrebuje vidět)  
        autorizuj(['Admin', 'Vyroba', 'Obchod', 'Odberatel']); // Logistika to nepotrebuje vidieť
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->index();
        break;

    case 'create_complaint':    // funkcia pre formulár reklamácií
        autorizuj(['Odberatel', 'Admin', 'Obchod']);
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->create();
        break;

    case 'store_complaint':   // funkcia pro uložení reklamace do databázy
        autorizuj(['Odberatel', 'Admin', 'Obchod']);
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->store();
        break;

    case 'view_complaint':
        autorizuj(['Admin', 'Vyroba', 'Obchod', 'Odberatel']);
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->show();
        break;

    case 'update_complaint_status':
        autorizuj(['Admin', 'Vyroba', 'Obchod']); // Odberateľ nemôže meniť stav!
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->updateStatus();
        break;
    
    case 'view_complaint':
        autorizuj(['Admin', 'Vyroba', 'Obchod', 'Odberatel']);
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->show();
        break;

    case 'update_complaint_status':
        autorizuj(['Admin', 'Vyroba', 'Obchod']);
        require_once 'controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->updateStatus();
        break;
    
    case 'add_termin':
        autorizuj(['Admin', 'Obchod', 'Vyroba', 'Logistika', 'Doprava']);
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->addTermin();
        break;

    case 'complete_termin':
        autorizuj(['Admin', 'Obchod', 'Vyroba', 'Logistika', 'Doprava']);
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->completeTermin();
        break;

    case 'delete_termin':
        autorizuj(['Admin', 'Obchod', 'Vyroba']); // Len vyššia moc môže mazať!
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->deleteTermin();
        break;

default:
        // Namiesto surového echa načítame peknú šablónu cez náš hlavný layout
        $view = 'views/errors/404.php';
        require_once 'views/layouts/main.php';
        break;
}
