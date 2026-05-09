<?php $user = $_SESSION['user'] ?? null; ?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">Vitajte, <?= htmlspecialchars($user['meno']) ?>!</h2>
        <p class="text-muted">Tu je prehľad vášho narezového centra na dnes.</p>
    </div>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-danger shadow-sm border-0 mb-4"> // OPRAVA: Prístup zamietnutý - zobrazenie tejto sekcie je obmedzené len pre administrátorov. ////
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Prístup zamietnutý!</strong> Na zobrazenie tejto sekcie nemáte administrátorské oprávnenia.
        </div>
    <?php endif; ?>
</div>


<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="text-uppercase opacity-75 small fw-bold">Moje objednávky</h6>
                <h2 class="display-5 fw-bold"><?= $s['celkom'] ?? 0 ?></h2>
                <p class="mb-0 small"><i class="bi bi-clock-history"></i> Celkový počet zákaziek</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <h6 class="text-uppercase opacity-75 small fw-bold">Nové na spracovanie</h6>
                <h2 class="display-5 fw-bold"><?= $s['nove'] ?? 0 ?></h2>
                <p class="mb-0 small"><i class="bi bi-exclamation-circle"></i> Čakajú na začiatok výroby</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <h6 class="text-uppercase opacity-75 small fw-bold">Hodnota zákaziek</h6>
                <h2 class="display-5 fw-bold"><?= number_format($s['suma_celkom'] ?? 0, 2, ',', ' ') ?> €</h2>
                <p class="mb-0 small"><i class="bi bi-wallet2"></i> Celkový obrat bez DPH</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar3 me-2"></i> Termínovník (Najbližšie odovzdania)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">TERMÍN</th>
                                <th>PROJEKT</th>
                                <th>STAV</th>
                                <th class="text-end pe-3">AKCIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($terminovnik)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Žiadne blízke termíny.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($terminovnik as $t):
                                    $dnes = new DateTime();
                                    $termin = new DateTime($t['datum_cas_od']); // OPRAVA: Používame datum_cas_od z tabuľky TERMIN
                                    $diff = $dnes->diff($termin)->days;
                                    $color = ($diff <= 2) ? 'danger' : 'warning';
                                ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="badge bg-<?= $color ?>-subtle text-<?= $color ?> p-2">
                                                <?= date('d.m.Y H:i', strtotime($t['datum_cas_od'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($t['nazov_projektu']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($t['cislo_objednavky']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($t['termin_stav'] ?? 'Naplánovaný') ?></span></td>
                                        <td class="text-end pe-3">
                                            <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-4 text-center">
                <h5 class="fw-bold mb-3">Potrebujete narezť?</h5>
                <a href="index.php?page=new_order" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-plus-circle me-2"></i> Vytvoriť novú objednávku
                </a>
            </div>
        </div>
    </div>
</div>