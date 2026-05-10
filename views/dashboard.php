<?php
// Rozdelenie termínov do Semaforu
$kriticke = [];
$dnes = [];
$buducnost = [];

$dnesnyDatum = date('Y-m-d');

foreach ($aktivneTerminy as $t) {
    $datumTerminu = date('Y-m-d', strtotime($t['datum_cas_od']));
    
    if ($datumTerminu < $dnesnyDatum) {
        $kriticke[] = $t; // Všetko pred dneškom je kritické
    } elseif ($datumTerminu === $dnesnyDatum) {
        $dnes[] = $t; // Dnešný plán
    } else {
        $buducnost[] = $t; // Výhľad
    }
}
?>

<?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod', 'Vyroba', 'Logistika', 'Doprava'])): ?>
    <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-stoplights me-2"></i>Operačný semafor termínov</h5>
    
    <div class="row g-4 mb-4">
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 border-top border-danger border-3 bg-danger bg-opacity-10">
                <div class="card-header bg-transparent border-0 fw-bold text-danger pt-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Kritické / Zmeškané
                    <span class="badge bg-danger rounded-pill float-end"><?= count($kriticke) ?></span>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($kriticke)): ?>
                        <div class="text-center text-muted small py-3">Žiadne kritické termíny. Skvelá práca!</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-1">
                            <?php foreach ($kriticke as $t): ?>
                                <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="list-group-item list-group-item-action rounded border-0 shadow-sm small">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-danger"><?= htmlspecialchars($t['typ_terminu']) ?></strong>
                                        <span class="text-danger fw-bold"><?= date('d.m.', strtotime($t['datum_cas_od'])) ?></span>
                                    </div>
                                    <div class="mb-1 fw-bold"><?= htmlspecialchars($t['cislo_objednavky']) ?></div>
                                    <div class="text-muted text-truncate" style="font-size: 0.8rem;"><?= htmlspecialchars($t['obchodny_nazov'] ?? 'Neznámy zákazník') ?></div>
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
                    <i class="bi bi-calendar-day-fill text-warning me-2"></i>Dnešný plán
                    <span class="badge bg-warning text-dark rounded-pill float-end"><?= count($dnes) ?></span>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($dnes)): ?>
                        <div class="text-center text-muted small py-3">Na dnes nie sú naplánované žiadne úlohy.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-1">
                            <?php foreach ($dnes as $t): ?>
                                <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="list-group-item list-group-item-action rounded border-0 shadow-sm small">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-dark"><?= htmlspecialchars($t['typ_terminu']) ?></strong>
                                        <span class="text-warning fw-bold" style="text-shadow: 0 0 1px #000;"><?= date('H:i', strtotime($t['datum_cas_od'])) ?></span>
                                    </div>
                                    <div class="mb-1 fw-bold"><?= htmlspecialchars($t['cislo_objednavky']) ?></div>
                                    <div class="text-muted text-truncate" style="font-size: 0.8rem;"><?= htmlspecialchars($t['obchodny_nazov'] ?? 'Neznámy zákazník') ?></div>
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
                    <i class="bi bi-calendar-week-fill me-2"></i>Výhľad (Ďalšie dni)
                    <span class="badge bg-success rounded-pill float-end"><?= count($buducnost) ?></span>
                </div>
                <div class="card-body p-2">
                    <?php if (empty($buducnost)): ?>
                        <div class="text-center text-muted small py-3">Čistý stôl na ďalšie dni.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-1">
                            <?php 
                            // Obmedzíme výhľad napr. len na prvých 7 termínov, aby sa stĺpec zbytočne nenaťahoval
                            $buducnostLimit = array_slice($buducnost, 0, 7);
                            foreach ($buducnostLimit as $t): 
                            ?>
                                <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="list-group-item list-group-item-action rounded border-0 shadow-sm small">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-success"><?= htmlspecialchars($t['typ_terminu']) ?></strong>
                                        <span class="text-success fw-bold"><?= date('d.m.', strtotime($t['datum_cas_od'])) ?></span>
                                    </div>
                                    <div class="mb-1 fw-bold"><?= htmlspecialchars($t['cislo_objednavky']) ?></div>
                                </a>
                            <?php endforeach; ?>
                            <?php if (count($buducnost) > 7): ?>
                                <div class="text-center mt-2 small text-muted">
                                    ... a ďalších <?= count($buducnost) - 7 ?> termínov
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>