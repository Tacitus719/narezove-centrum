<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="index.php?page=orders" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Späť na zoznam</a>
        <h2 class="mb-0">Detail objednávky <?= htmlspecialchars($order['cislo_objednavky']) ?></h2>
    </div>
    <div class="text-end">
        <span class="badge bg-info fs-6"><?= $order['stav'] ?></span>
        <div class="text-muted small">Vytvorené: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
        <?php if (!empty($order['updated_at'])): ?>
            <div class="text-muted small" title="Posledná úprava">Aktualizované: <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></div>
        <?php endif; ?>
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
        <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba', 'Logistika', 'Obchod'])): ?>
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-truck me-2"></i> Správa stavu a logistiky
                </div>
                <div class="card-body">
                    <form action="index.php?page=update_order_status" method="POST">
                        <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">
                        
                        <label class="form-label small fw-bold">Zmeniť stav na:</label>
                        <select name="novy_stav" id="statusSelector" class="form-select mb-3">
                            <option value="<?= $order['stav'] ?>" selected><?= $order['stav'] ?> (Aktuálny)</option>
                            <option disabled>──────────</option>
                            
                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod']) && $order['stav'] == 'Nová'): ?>
                                <option value="Vo výrobe">🚀 Začať výrobu (Vo výrobe)</option>
                            <?php endif; ?>

                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba']) && $order['stav'] == 'Vo výrobe'): ?>
                                <option value="Vyrobená">✅ Dokončiť výrobu (Vyrobená)</option>
                            <?php endif; ?>

                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Logistika']) && $order['stav'] == 'Vyrobená'): ?>
                                <option value="Expedovaná">📦 Expedovať (Výdaj)</option>
                            <?php endif; ?>

                            <?php if ($_SESSION['user']['rola'] === 'Admin'): ?>
                                <option value="Zrušená">Zrušená</option>
                            <?php endif; ?>
                        </select>

                        <div class="mb-3 d-none" id="vehicleSelection">
                            <label class="form-label small fw-bold text-success">Priradiť vozidlo:</label>
                            <select name="id_vozidlo" class="form-select border-success">
                                <option value="">-- Vyberte vozidlo zo zoznamu --</option>
                                <?php if (!empty($vozidla)): ?>
                                    <?php foreach ($vozidla as $v): ?>
                                        <option value="<?= $v['id_vozidlo'] ?>">
                                            <?= htmlspecialchars($v['znacka_model']) ?> (<?= htmlspecialchars($v['spz']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if ($_SESSION['user']['rola'] === 'Admin' || in_array($order['stav'], ['Nová', 'Vo výrobe', 'Vyrobená'])): ?>
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                Potvrdiť zmenu
                            </button>
                        <?php else: ?>
                            <div class="alert alert-success small mb-0 py-2">Práca na zákazke je hotová.</div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <script>
            document.getElementById('statusSelector').addEventListener('change', function() {
                const vSelect = document.getElementById('vehicleSelection');
                if (this.value === 'Expedovaná') {
                    vSelect.classList.remove('d-none');
                    vSelect.querySelector('select').setAttribute('required', 'required');
                } else {
                    vSelect.classList.add('d-none');
                    vSelect.querySelector('select').removeAttribute('required');
                }
            });
            </script>
        <?php endif; ?>
</div> <!-- /col-md-3 -->
</div> <!-- /row -->