<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROMA B2B Systém</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #212529; color: white; width: 260px; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 12px 20px; font-weight: 500;}
        .sidebar a:hover, .sidebar a.active { background-color: #343a40; color: #fff; border-left: 4px solid #0d6efd; }
        .main-content { flex-grow: 1; }
        .topbar { background-color: white; border-bottom: 1px solid #e9ecef; }
    </style>
</head>
<body class="d-flex">

<?php
$user = $_SESSION['user'] ?? null;
?>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary text-center">
            <h4 class="m-0 text-white">PROMA</h4>
            <small class="text-muted">Nárezové centrum</small>
        </div>
        
        <div class="mt-3">
            <a href="index.php?page=dashboard" class="active"><i class="bi bi-grid-1x2 me-2"></i> Nástenka (Dashboard)</a>
            <a href="index.php?page=orders"><i class="bi bi-list-check me-2"></i> Moje objednávky</a>
            <a href="index.php?page=new_order" class="text-warning"><i class="bi bi-plus-square me-2"></i> Nová objednávka</a>
            <a href="index.php?page=claims"><i class="bi bi-tools me-2"></i> Reklamácie</a>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['rola'] === 'Admin'): ?>
                <div class="mt-3">
                    <h6 class="ps-3 text-uppercase text-muted small fw-bold">Administrácia</h6>
                        <a class="text-primary fw-bold" href="index.php?page=admin_customers">
                            <i class="bi bi-building me-2"></i> Správa odberateľov
                        </a>
                </div>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link text-primary" href="index.php?page=admin_materials">
                    <i class="bi bi-layers me-2"></i> Katalóg materiálov
                </a>
            </li>
        </div>
        
        <div class="mt-auto mb-3">
            <a href="index.php?page=logout" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i> Odhlásiť sa</a>
        </div>
    </div>

    <div class="main-content d-flex flex-column">
        
        <div class="topbar p-3 d-flex justify-content-end align-items-center shadow-sm">
            <div class="dropdown">
                <span class="me-3">
                    <i class="bi bi-person-circle fs-5 me-1 align-middle"></i>
                    <strong><?php echo $user ? htmlspecialchars($user['meno'] . ' ' . $user['priezvisko']) : 'Používateľ'; ?></strong>
                    <span class="badge bg-secondary ms-1"><?php echo $user ? htmlspecialchars($user['rola']) : ''; ?></span>
                </span>
            </div>
        </div>

        <div class="p-4">
            <?php 
                // Toto je to kúzlo - sem sa vstrekne obsah napr. z views/dashboard/index.php
                if (isset($view) && $view) {
                    require_once $view;
                } else {
                    echo '<div class="alert alert-danger">Chýba obsah stránky (&dollar;view nie je definované).</div>';
                }
            ?>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>