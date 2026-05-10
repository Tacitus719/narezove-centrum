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

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-bold py-3">
        <i class="bi bi-calendar3 me-2 text-primary"></i> Najbližšie naplánované termíny
    </div>
    <div class="card-body p-0">
        <?php if (!empty($terminy)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <tbody>
                        <?php foreach ($terminy as $t): ?>
                            <tr>
                                <!-- Termín udalosti -->
                                <td class="text-nowrap ps-4" style="width: 180px;">
                                    <i class="bi bi-clock text-primary me-2"></i>
                                    <strong><?= date('d.m.Y', strtotime($t['datum_cas_od'])) ?></strong>
                                    <span class="text-muted ms-1"><?= date('H:i', strtotime($t['datum_cas_od'])) ?></span>
                                </td>
                                
                                <!-- Informácie o objednávke a odberateľovi -->
                                <td>
                                    <div class="fw-bold mb-1">
                                        <a href="index.php?page=view_order&id=<?= $t['id_objednavka'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($t['cislo_objednavky']) ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="bi bi-building me-1"></i> 
                                        <?= htmlspecialchars($t['obchodny_nazov'] ?? 'Interná zákazka') ?>
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-folder2-open me-1"></i> 
                                        <?= htmlspecialchars($t['nazov_projektu']) ?>
                                    </div>
                                </td>

                                <!-- Stav termínu -->
                                <td class="text-end pe-4">
                                    <?php
                                        $stavClass = 'bg-secondary';
                                        if ($t['stav_terminu'] === 'Naplánovaný') $stavClass = 'bg-info text-dark';
                                        if ($t['stav_terminu'] === 'Prebieha') $stavClass = 'bg-warning text-dark';
                                        if ($t['stav_terminu'] === 'Dokončený') $stavClass = 'bg-success';
                                    ?>
                                    <span class="badge <?= $stavClass ?> shadow-sm">
                                        <?= htmlspecialchars($t['stav_terminu']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                Aktuálne nie sú naplánované žiadne nadchádzajúce udalosti.
            </div>
        <?php endif; ?>
    </div>
</div>


    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-4 text-center">
                <h5 class="fw-bold mb-3">Sem môžete vkladať nové objednávky</h5>
                <a href="index.php?page=new_order" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-plus-circle me-2"></i> Vytvoriť novú objednávku
                </a>
            </div>
        </div>
    </div>
</div>