<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROMA B2B Systém</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
            width: 260px;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            font-weight: 500;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #343a40;
            color: #fff;
            border-left: 4px solid #0d6efd;
        }

        .main-content {
            flex-grow: 1;
        }

        <div class="topbar p-3 d-flex justify-content-end align-items-center shadow-sm bg-white"><div class="d-flex align-items-center"><span class="me-4 border-end pe-4"><i class="bi bi-person-circle fs-5 me-1 align-middle text-primary"></i><strong class="align-middle"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['meno'] . ' ' . $_SESSION['user']['priezvisko']) : 'Používateľ'; ?></strong><span class="badge bg-secondary ms-2 align-middle"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['rola']) : ''; ?></span></span><a href="index.php?page=logout" class="btn btn-sm btn-outline-danger shadow-sm"><i class="bi bi-box-arrow-right me-1"></i>Odhlásiť sa </a></div></div>
    </style>
</head>

<body class="d-flex">

    <?php
    $user = $_SESSION['user'] ?? null;
    ?>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary text-center">
            <h4 class="m-0 text-white">nárezove centrum</h4>
        </div>
        <div class="text-center py-4 border-bottom border-secondary mb-3">
            <a href="index.php">
                <img src="public/logo.png" alt="PROMA Logo" style="max-height: 45px; max-width: 90%; object-fit: contain;">
            </a>
        </div>

        <div class="mt-3">
            <a href="index.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2 me-2"></i> Nástenka (Dashboard)
            </a>

            <a href="index.php?page=orders" class="<?= $page == 'orders' ? 'active' : '' ?>">
                <i class="bi bi-list-check me-2"></i>
                <?= in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba', 'Obchod']) ? 'Všetky objednávky' : 'Moje objednávky' ?>
            </a>

            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Odberatel'])): ?>
                <a href="index.php?page=new_order" class="text-warning <?= $page == 'new_order' ? 'active' : '' ?>">
                    <i class="bi bi-plus-square me-2"></i> Nová objednávka
                </a>
            <?php endif; ?>

            <a href="index.php?page=complaints" class="<?= $page == 'complaints' ? 'active' : '' ?>">
                <i class="bi bi-tools me-2"></i> Reklamácie
            </a>

            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod'])): ?>
                <div class="mt-4">
                    <h6 class="ps-3 text-uppercase text-muted small fw-bold">Katalógy</h6>
                    <a href="index.php?page=admin_materials" class="<?= $page == 'admin_materials' ? 'active' : '' ?>">
                        <i class="bi bi-layers me-2"></i> Plošný materiál
                    </a>
                    <a href="index.php?page=admin_edges" class="<?= $page == 'admin_edges' ? 'active' : '' ?>">
                        <i class="bi bi-usb-drive me-2"></i> ABS Hrany
                    </a>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['rola'] === 'Admin'): ?>
                <div class="mt-4">
                    <h6 class="ps-3 text-uppercase text-muted small fw-bold">Administrácia</h6>
                    <a href="index.php?page=admin_customers" class="<?= $page == 'admin_customers' ? 'active' : '' ?>">
                        <i class="bi bi-building me-2"></i> Správa odberateľov
                    </a>
                    <a href="index.php?page=admin_staff" class="<?= $page == 'admin_staff' ? 'active' : '' ?>">
                        <i class="bi bi-people-fill me-2"></i> Personál a role
                    </a>
                    <a href="index.php?page=admin_vehicles" class="<?= $page == 'admin_vehicles' ? 'active' : '' ?>">
                        <i class="bi bi-truck me-2"></i> Správa vozového parku
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="main-content d-flex flex-column">

        <div class="topbar p-3 d-flex justify-content-end align-items-center shadow-sm bg-white">
            <div class="d-flex align-items-center">
                <span class="me-4 border-end pe-4">
                    <i class="bi bi-person-circle fs-5 me-1 align-middle text-primary"></i>
                    <strong class="align-middle"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['meno'] . ' ' . $_SESSION['user']['priezvisko']) : 'Používateľ'; ?></strong>
                    <span class="badge bg-secondary ms-2 align-middle"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['rola']) : ''; ?></span>
                </span>

                <a href="index.php?page=logout" class="btn btn-sm btn-outline-danger shadow-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Odhlásiť sa
                </a>
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