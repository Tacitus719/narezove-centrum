<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="index.php?page=orders" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Späť na zoznam</a>
        <h2 class="mb-0">Detail objednávky <?= htmlspecialchars($order['cislo_objednavky']) ?></h2>
    </div>
    <div class="text-end">
        <span class="badge bg-info fs-6"><?= $order['stav'] ?></span>
        <div class="text-muted small">Vytvorené: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Položky k narezaniu</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle small">
                    <thead class="table-light text-muted" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-3">NÁZOV DIELCA</th>
                            <th>ROZMER (mm)</th>
                            <th class="text-center">KS</th>
                            <th>MATERIÁL (DEKOR)</th>
                            <th>HRANY (H-D-Ľ-P)</th>
                            <th class="text-center">ATYP</th>
                            <th class="text-end pe-3">CENA SPOLU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): 
                            // --- MATEMATIKA CENY PRE ZOBRAZENIE ---
                            $plochaM2 = ($item['dlzka_mm'] * $item['sirka_mm']) / 1000000;
                            $cenaDosky = $plochaM2 * ($item['material_cena'] ?? 0);
                            
                            $metrazHranMm = 0;
                            if ($item['id_horna_hrana']) $metrazHranMm += $item['dlzka_mm'];
                            if ($item['id_dolna_hrana']) $metrazHranMm += $item['dlzka_mm'];
                            if ($item['id_lava_hrana']) $metrazHranMm += $item['sirka_mm'];
                            if ($item['id_prava_hrana']) $metrazHranMm += $item['sirka_mm'];
                            
                            $cenaHran = ($metrazHranMm / 1000) * ($item['hrana_cena'] ?? 0);
                            $cenaPolozky = ($cenaDosky + $cenaHran) * $item['pocet_kusov'];
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= htmlspecialchars($item['nazov_dielu']) ?></td>
                            <td><span class="text-primary"><?= $item['dlzka_mm'] ?></span> x <span class="text-primary"><?= $item['sirka_mm'] ?></span></td>
                            <td class="text-center fw-bold"><?= $item['pocet_kusov'] ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($item['material_nazov']) ?></div>
                                <div class="badge bg-light text-dark border"><?= htmlspecialchars($item['material_kod']) ?></div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="d-flex gap-1 mb-1">
                                        <span class="badge <?= $item['id_horna_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">H</span>
                                        <span class="badge <?= $item['id_dolna_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">D</span>
                                        <span class="badge <?= $item['id_lava_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">Ľ</span>
                                        <span class="badge <?= $item['id_prava_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">P</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($item['hrana_nazov'] ?? 'Bez hrany') ?></small>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($item['priloha_dielu']): ?>
                                    <a href="<?= htmlspecialchars($item['priloha_dielu']) ?>" target="_blank" class="text-info"><i class="bi bi-file-earmark-pdf fs-5"></i></a>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-end pe-3 fw-bold"><?= number_format($cenaPolozky, 2, ',', ' ') ?> €</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-3">
            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod', 'Vyroba'])): ?>
                <div class="card shadow-sm border-primary mb-4">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-gear-fill me-2"></i> Správa objednávky
                    </div>
                    <div class="card-body">
                        <form action="index.php?page=update_order_status" method="POST">
                            <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">
                            <label class="form-label small fw-bold">Zmeniť stav zákazky:</label>
                                <select name="novy_stav" class="form-select form-select-sm mb-3">
                                    <option value="Nová" <?= $order['stav'] == 'Nová' ? 'selected' : '' ?>>Nová</option>
                                    <option value="Vo výrobe" <?= $order['stav'] == 'Vo výrobe' ? 'selected' : '' ?>>Vo výrobe</option>
                                    <option value="Expedovaná" <?= $order['stav'] == 'Expedovaná' ? 'selected' : '' ?>>Expedovaná</option>
                                    <option value="Zrušená" <?= $order['stav'] == 'Zrušená' ? 'selected' : '' ?>>Zrušená</option>
                                </select>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                Aktualizovať stav
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            
        
        <div class="card shadow-sm border-0 mb-4 text-center py-3 bg-primary text-white">
            <div class="card-body">
                <div class="small opacity-75">Celková cena objednávky</div>
                <div class="h2 mb-0 fw-bold"><?= number_format($order['celkova_suma'], 2, ',', ' ') ?> €</div>
                <div class="small opacity-75">bez DPH</div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informácie</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Poznámka:</small>
                    <div class="small"><?= nl2br(htmlspecialchars($order['poznamka'])) ?: '<em>Bez poznámky</em>' ?></div>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span>Odhadovaný čas výroby:</span>
                    <span class="fw-bold"><?= $order['celkovy_cas_vyroby_min'] ?> min</span>
                </div>
                <hr>
                <button onclick="window.print()" class="btn btn-dark w-100 btn-sm">
                    <i class="bi bi-printer me-2"></i> Vytlačiť podklady
                </button>
            </div>
        </div>
    </div>
</div>