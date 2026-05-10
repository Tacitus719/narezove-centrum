<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Správa vozového parku</h2>
        <small class="text-muted">Evidencia vozidiel pre rozvoz zákaziek</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
        <i class="bi bi-truck me-2"></i> Pridať vozidlo
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i> Údaje o vozidle boli úspešne uložené.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ŠPZ</th>
                        <th>Značka a Model</th>
                        <th class="text-end">Nosnosť (kg)</th>
                        <th class="text-end">Objem (m³)</th>
                        <th class="text-center">Aktuálny stav</th>
                        <th>Status v evidencii</th>
                        <th class="text-end pe-4">Akcia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td class="ps-4 fw-bold">
                                <span class="badge border border-dark text-dark bg-white shadow-sm fs-6" style="font-family: monospace;">
                                    <?= htmlspecialchars($v['spz']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($v['znacka_model']) ?></td>
                            <td class="text-end"><?= number_format($v['nosnost_kg'], 0, ',', ' ') ?> kg</td>
                            <td class="text-end"><?= number_format($v['objem_m3'], 2, ',', ' ') ?> m³</td>
                            <td class="text-center">
                                <?php
                                    $stavClass = 'bg-secondary';
                                    if ($v['stav'] === 'Dostupné') $stavClass = 'bg-success';
                                    if ($v['stav'] === 'V servise') $stavClass = 'bg-danger';
                                    if ($v['stav'] === 'Na ceste') $stavClass = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $stavClass ?>"><?= htmlspecialchars($v['stav']) ?></span>
                            </td>
                            <td>
                                <?php if ($v['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktívne</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Vyradené</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary edit-vehicle-btn" 
                                    data-id="<?= $v['id_vozidlo'] ?>"
                                    data-spz="<?= htmlspecialchars($v['spz']) ?>"
                                    data-znacka="<?= htmlspecialchars($v['znacka_model']) ?>"
                                    data-nosnost="<?= $v['nosnost_kg'] ?>"
                                    data-objem="<?= $v['objem_m3'] ?>"
                                    data-stav="<?= $v['stav'] ?>"
                                    data-active="<?= $v['is_active'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">V systéme zatiaľ nie sú evidované žiadne vozidlá.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="vehicleForm" action="index.php?page=save_vehicle" method="POST" class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-truck me-2"></i> Pridať nové vozidlo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_vozidlo" id="edit_vehicle_id">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Evidenčné číslo (ŠPZ) *</label>
                    <input type="text" name="spz" class="form-control text-uppercase" placeholder="BA123XY" required maxlength="15">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Značka a model *</label>
                    <input type="text" name="znacka_model" class="form-control" placeholder="napr. Iveco Daily" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nosnosť (kg) *</label>
                        <input type="number" name="nosnost_kg" class="form-control" required min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Objem ložného p. (m³) *</label>
                        <input type="number" step="0.1" name="objem_m3" class="form-control" required min="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Aktuálny stav *</label>
                    <select name="stav" class="form-select" required>
                        <option value="Dostupné">Dostupné (Na firme)</option>
                        <option value="Na ceste">Na ceste</option>
                        <option value="V servise">V servise</option>
                    </select>
                </div>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="vehicleActive" checked>
                    <label class="form-check-label fw-bold" for="vehicleActive">Vozidlo je aktívne v evidencii</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary">Uložiť údaje</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-vehicle-btn').forEach(button => {
    button.addEventListener('click', function() {
        const f = document.getElementById('vehicleForm');
        document.getElementById('modalTitle').innerText = 'Upraviť vozidlo';
        f.action = 'index.php?page=update_vehicle';
        
        document.getElementById('edit_vehicle_id').value = this.dataset.id;
        f.querySelector('[name="spz"]').value = this.dataset.spz;
        f.querySelector('[name="znacka_model"]').value = this.dataset.znacka;
        f.querySelector('[name="nosnost_kg"]').value = this.dataset.nosnost;
        f.querySelector('[name="objem_m3"]').value = this.dataset.objem;
        f.querySelector('[name="stav"]').value = this.dataset.stav;
        document.getElementById('vehicleActive').checked = (this.dataset.active == '1');
    });
});

document.querySelector('[data-bs-target="#addVehicleModal"]:not(.edit-vehicle-btn)').addEventListener('click', function() {
    const f = document.getElementById('vehicleForm');
    document.getElementById('modalTitle').innerText = 'Pridať nové vozidlo';
    f.action = 'index.php?page=save_vehicle';
    f.reset();
    document.getElementById('edit_vehicle_id').value = '';
});
</script>