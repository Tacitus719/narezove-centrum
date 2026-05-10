<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba', 'Obchod', 'Logistika', 'Doprava']) ? 'Všetky objednávky' : 'Moje objednávky' ?></h2>
    <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Odberatel'])): ?>
        <a href="index.php?page=new_order" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> Nová objednávka
        </a>
    <?php endif; ?>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light text-secondary small">
                    <tr>
                        <th class="ps-4">ČÍSLO</th>
                        <th>NÁZOV PROJEKTU</th>
                        <th>DÁTUM</th>
                        <th class="text-center">STAV</th>
                        <th class="text-end">SUMA</th>
                        <th class="text-end pe-4">AKCIE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($order['cislo_objednavka'] ?? $order['cislo_objednavky']) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['nazov_projektu']) ?>
                                    <?php if (isset($order['obchodny_nazov'])): ?>
                                        <br><small class="text-muted"><i class="bi bi-building"></i> <?= htmlspecialchars($order['obchodny_nazov']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d.m.Y', strtotime($order['created_at'])) ?>
                                    <?php if (!empty($order['updated_at'])): ?>
                                        <br><small class="text-muted" title="Posledná zmena"><i class="bi bi-clock-history"></i> <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></small>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    $stav = $order['stav']; 
                                    switch ($stav) {
                                        case 'Nová': $statusClass = 'bg-info text-dark'; break;
                                        case 'Vo výrobe': $statusClass = 'bg-warning text-dark'; break;
                                        case 'Vyrobená': $statusClass = 'bg-primary'; break;
                                        case 'Expedovaná': $statusClass = 'bg-success'; break;
                                        case 'Zrušená': $statusClass = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?> shadow-sm">
                                        <?= htmlspecialchars($stav) ?>
                                    </span>
                                    
                                    <?php if (!empty($order['spz'])): ?>
                                        <div class="mt-1 small text-muted" title="<?= htmlspecialchars($order['znacka_model']) ?>">
                                            <i class="bi bi-truck"></i> <?= htmlspecialchars($order['spz']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="fw-bold text-end"><?= number_format($order['celkova_suma'], 2, ',', ' ') ?> €</td>

                                <td class="text-end pe-4">
                                    <a href="index.php?page=view_order&id=<?= $order['id_objednavka'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if ($order['stav'] === 'Nová' && in_array($_SESSION['user']['rola'], ['Admin', 'Odberatel'])): ?>
                                        <a href="index.php?page=edit_order&id=<?= $order['id_objednavka'] ?>" class="btn btn-sm btn-outline-warning ms-1">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Nenašli sa žiadne objednávky.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>