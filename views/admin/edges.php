<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Správa ABS hrán</h2>
        <small class="text-muted">Evidencia a cenník hránovacích pások</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEdgeModal">
        <i class="bi bi-plus-lg me-2"></i> Pridať hranu
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i> Údaje o ABS hrane boli úspešne uložené.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light text-secondary small">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>KÓD</th>
                        <th>NÁZOV HRANY</th>
                        <th>ROZMER (mm)</th>
                        <th class="text-end">CENA ZA BM (€)</th>
                        <th class="text-center">STAV</th>
                        <th class="text-end pe-4">AKCIE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($edges)): ?>
                        <?php foreach ($edges as $edge): ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= $edge['id_hrana'] ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($edge['kod_vyrobcu']) ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($edge['nazov']) ?></td>
                            <td><?= number_format($edge['hrubka_mm'], 1, '.', '') ?> x <?= $edge['sirka_mm'] ?></td>
                            <td class="text-end fw-bold"><?= number_format($edge['cena_bm'], 2, ',', ' ') ?> €</td>
                            <td class="text-center">
                                <?php if ($edge['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktívna v ponuke</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Neaktívna</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary edit-edge-btn" 
                                    data-id="<?= $edge['id_hrana'] ?>"
                                    data-kod="<?= htmlspecialchars($edge['kod_vyrobcu']) ?>"
                                    data-nazov="<?= htmlspecialchars($edge['nazov']) ?>"
                                    data-hrubka="<?= $edge['hrubka_mm'] ?>"
                                    data-sirka="<?= $edge['sirka_mm'] ?>"
                                    data-cena="<?= $edge['cena_bm'] ?>"
                                    data-active="<?= $edge['is_active'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#addEdgeModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">V systéme zatiaľ nie sú evidované žiadne ABS hrany.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addEdgeModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="edgeForm" action="index.php?page=save_edge" method="POST" class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-rulers me-2"></i> Pridať novú ABS hranu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_hrana" id="edit_edge_id">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kód výrobcu</label>
                        <input type="text" name="kod_vyrobcu" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Názov hrany (Dekor / Farba)</label>
                        <input type="text" name="nazov" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Hrúbka [mm]</label>
                        <input type="number" step="0.1" name="hrubka_mm" class="form-control" placeholder="napr. 2.0" required min="0.1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Šírka [mm]</label>
                        <input type="number" name="sirka_mm" class="form-control" placeholder="napr. 22" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cena / bm *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="cena_bm" class="form-control" required min="0">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edgeActive" checked>
                    <label class="form-check-label fw-bold" for="edgeActive">Zobraziť hranu v ponuke pre zákazníkov</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary">Uložiť hranu</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-edge-btn').forEach(button => {
    button.addEventListener('click', function() {
        const f = document.getElementById('edgeForm');
        document.getElementById('modalTitle').innerText = 'Upraviť ABS hranu';
        f.action = 'index.php?page=update_edge';
        document.getElementById('edit_edge_id').value = this.dataset.id;
        f.querySelector('[name="kod_vyrobcu"]').value = this.dataset.kod;
        f.querySelector('[name="nazov"]').value = this.dataset.nazov;
        f.querySelector('[name="hrubka_mm"]').value = this.dataset.hrubka;
        f.querySelector('[name="sirka_mm"]').value = this.dataset.sirka;
        f.querySelector('[name="cena_bm"]').value = this.dataset.cena;
        document.getElementById('edgeActive').checked = (this.dataset.active == '1');
    });
});

document.querySelector('[data-bs-target="#addEdgeModal"]:not(.edit-edge-btn)').addEventListener('click', function() {
    const f = document.getElementById('edgeForm');
    document.getElementById('modalTitle').innerText = 'Pridať novú ABS hranu';
    f.action = 'index.php?page=save_edge';
    f.reset();
    document.getElementById('edit_edge_id').value = '';
});
</script>
