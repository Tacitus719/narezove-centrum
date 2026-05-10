<?php
/**
 * views/dashboard/index.php
 * Hlavná nástenka so systémom Semafor
 */

// 1. Rozdelenie termínov do kategórií Semaforu
$kriticke = [];
$dnes = [];
$buducnost = [];

$dnesnyDatum = date('Y-m-d');

if (isset($aktivneTerminy) && is_array($aktivneTerminy)) {
    foreach ($aktivneTerminy as $t) {
        $datumTerminu = date('Y-m-d', strtotime($t['datum_cas_od']));
        
        if ($datumTerminu < $dnesnyDatum) {
            $kriticke[] = $t; // Sklz
        } elseif ($datumTerminu === $dnesnyDatum) {
            $dnes[] = $t; // Dnes
        } else {
            $buducnost[] = $t; // Budúcnosť
        }
    }
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h2 class="fw-bold text-dark mb-0">Dashboard</h2>
            <p class="text-muted">Vitajte v systéme PROMA, <?= htmlspecialchars($_SESSION['user']['meno']) ?>.</p>
        </div>
        <?php if (!$isStaff): ?>
            <a href="index.php?page=create_order" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Vytvoriť novú objednávku
            </a>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-cart-check text-primary fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Aktívne objednávky</div>
                        <div class="fs-4 fw-bold"><?= $s['celkom'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="bi bi-currency-euro text-success fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Celková hodnota</div>
                        <div class="fs-4 fw-bold"><?= number_format($s['suma_celkom'] ?? 0, 2, ',', ' ') ?> €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3 mt-4">
        <?php if ($isStaff): ?>
            <i class="bi bi-stoplights me-2 text-primary"></i>Operačný semafor termínov
        <?php else: ?>
            <i class="bi bi-clock-history me-2 text-primary"></i>Stav mojich objednávok
        <?php endif; ?>
    </h5>
    
    <div class="row g-4 mb-5">
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 border-top border-danger border-3 bg-danger bg-opacity-10">
                <div class="card-header bg-transparent border-0 fw-bold text-danger pt-3">
                    <?= $isStaff ? 'Kritické / Zmeškané' : 'Mierne zdržanie' ?>
                    <span class="badge bg-danger rounded-pill float-end"><?= count($kriticke) ?></span>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($kriticke)): ?>
                        <div class="text-center text-muted small py-4">Všetko stíhate. Skvelé!</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-2">
                            <?php foreach ($kriticke as $t): ?>
                                <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="list-group-item list-group-item-action rounded border-0 shadow-sm small">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-danger"><?= htmlspecialchars($t['typ_terminu']) ?></strong>
                                        <span class="text-danger fw-bold"><?= date('d.m.', strtotime($t['datum_cas_od'])) ?></span>
                                    </div>
                                    <div class="mb-0 fw-bold"><?= htmlspecialchars($t['cislo_objednavky']) ?></div>
                                    <?php if ($isStaff): ?>
                                        <div class="text-muted text-truncate" style="font-size: 0.75rem;"><?= htmlspecialchars($t['obchodny_nazov'] ?? 'Zákazník') ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 border-top border-warning border-3 bg-warning bg-opacity-10">
                <div class="card-header bg-transparent border-0 fw-bold text-dark pt-3">
                    <?= $isStaff ? 'Dnešný plán' : 'Aktuálne sa rieši dnes' ?>
                    <span class="badge bg-warning text-dark rounded-pill float-end"><?= count($dnes) ?></span>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($dnes)): ?>
                        <div class="text-center text-muted small py-4">Na dnes nie je nič naplánované.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-2">
                            <?php foreach ($dnes as $t): ?>
                                <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="list-group-item list-group-item-action rounded border-0 shadow-sm small">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-dark"><?= htmlspecialchars($t['typ_terminu']) ?></strong>
                                        <span class="text-warning fw-bold"><?= date('H:i', strtotime($t['datum_cas_od'])) ?></span>
                                    </div>
                                    <div class="fw-bold"><?= htmlspecialchars($t['cislo_objednavky']) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 border-top border-success border-3 bg-success bg-opacity-10">
                <div class="card-header bg-transparent border-0 fw-bold text-success pt-3">
                    <?= $isStaff ? 'Výhľad' : 'Naplánované termíny' ?>
                    <span class="badge bg-success rounded-pill float-end"><?= count($buducnost) ?></span>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($buducnost)): ?>
                        <div class="text-center text-muted small py-4">Žiadne ďalšie termíny.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-2">
                            <?php foreach (array_slice($buducnost, 0, 5) as $t): ?>
                                <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="list-group-item list-group-item-action rounded border-0 shadow-sm small">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-success"><?= htmlspecialchars($t['typ_terminu']) ?></strong>
                                        <span class="text-success fw-bold"><?= date('d.m.', strtotime($t['datum_cas_od'])) ?></span>
                                    </div>
                                    <div class="fw-bold"><?= htmlspecialchars($t['cislo_objednavky']) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>