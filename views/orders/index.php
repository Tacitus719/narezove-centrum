<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Moje objednávky</h2>
    <a href="index.php?page=new_order" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Nová objednávka
    </a>
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
                        <th class="text-end">SUMA</th>
                        <th class="text-end">VÝROBNÝ ČAS</th>
                        <th class="text-center">STAV</th>
                        <th class="text-end pe-4">AKCIE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">Zatiaľ ste nevytvorili žiadnu objednávku.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($o['cislo_objednavky']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($o['nazov_projektu']) ?></div>
                                    <small class="text-muted"><?= mb_strimwidth($o['poznamka'], 0, 50, "...") ?></small>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                                <td class="text-end fw-bold"><?= number_format($o['celkova_suma'], 2, ',', ' ') ?> €</td>
                                <td class="text-end"><?= $o['celkovy_cas_vyroby_min'] ?> min</td>
                                <td>
                                    <?php
                                    // Definujeme farbu na základe stavu
                                    $statusClass = 'bg-secondary'; // Predvolená šedá
                                    
                                    switch ($o['stav']) {
                                        case 'Nová':
                                            $statusClass = 'bg-info text-dark';
                                            break;
                                        case 'Vo výrobe':
                                            $statusClass = 'bg-warning text-dark';
                                            break;
                                        case 'Expedovaná':
                                            $statusClass = 'bg-success';
                                            break;
                                        case 'Zrušená':
                                            $statusClass = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?> shadow-sm">
                                        <?= htmlspecialchars($o['stav']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="index.php?page=view_order&id=<?= $o['id_objednavka'] ?>" class="btn btn-sm btn-outline-primary" title="Zobraziť detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if ($o['stav'] === 'Nová'): ?>
                                        <a href="index.php?page=edit_order&id=<?= $o['id_objednavka'] ?>" class="btn btn-sm btn-outline-warning" title="Editovať objednávku">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <a href="index.php?page=cancel_order&id=<?= $o['id_objednavka'] ?>" 
                                        class="btn btn-sm btn-outline-danger" 
                                        title="Zrušiť objednávku"
                                        onclick="return confirm('Naozaj chcete zrušiť túto objednávku?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>