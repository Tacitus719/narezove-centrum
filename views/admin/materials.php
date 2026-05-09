<?php 
$isAdmin = ($_SESSION['user']['rola'] === 'Admin'); 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Katalóg plošných materiálov</h2>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
            <i class="bi bi-plus-square"></i> Pridať nový materiál
        </button>
    <?php endif; ?>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Kód (Výrobca)</th>
                    <th>Názov dekoru</th>
                    <th>Typ</th>
                    <th>Formát (mm)</th>
                    <th>Cena / m²</th>
                    <th>Stav</th>
                    <th class="text-end pe-4"><?= $isAdmin ? 'Akcia' : '' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $m): ?>
                <tr>
                    <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($m['kod_vyrobcu']) ?></td>
                    <td><?= htmlspecialchars($m['nazov_dekoru']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $m['typ'] ?></span></td>
                    <td class="small"><?= $m['dlzka_mm'] ?> x <?= $m['sirka_mm'] ?> x <?= $m['hrubka_mm'] ?></td>
                    <td class="fw-bold"><?= number_format($m['cena_MJ'], 2, ',', ' ') ?> €</td>
                    <td>
                        <span class="badge bg-<?= $m['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $m['is_active'] ? 'Aktívny' : 'Vyradený' ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <?php if ($isAdmin): ?>
                            <button class="btn btn-sm btn-outline-primary edit-btn" 
                                data-id="<?= $m['id_material'] ?>"
                                data-kod="<?= htmlspecialchars($m['kod_vyrobcu']) ?>"
                                data-nazov="<?= htmlspecialchars($m['nazov_dekoru']) ?>"
                                data-typ="<?= $m['typ'] ?>"
                            data-hrubka="<?= $m['hrubka_mm'] ?>"
                            data-dlzka="<?= $m['dlzka_mm'] ?>"
                            data-sirka="<?= $m['sirka_mm'] ?>"
                            data-mj="<?= $m['mj'] ?>"
                            data-cena="<?= $m['cena_MJ'] ?>"
                            data-obrazok="<?= htmlspecialchars($m['obrazok_url']) ?>"
                            data-active="<?= $m['is_active'] ?>"
                            data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pre pridanie/úpravu materiálu -->
 <div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="index.php?page=save_material" method="POST" class="modal-content" id="materialForm">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Pridať nový materiál</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                <input type="hidden" name="id_material" id="edit_id_material">
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kód výrobcu (Dekor) *</label>
                        <input type="text" name="kod_vyrobcu" class="form-control" placeholder="napr. H1145 ST10" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Názov dekoru *</label>
                        <input type="text" name="nazov_dekoru" class="form-control" placeholder="napr. Dub Bardolino" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Typ materiálu *</label>
                        <select name="typ" class="form-select" required>
                            <option value="LDTD">LDTD (Laminovaná drevotrieska)</option>
                            <option value="MDF">MDF (Drevovláknitá doska)</option>
                            <option value="Pracovná doska">Pracovná doska</option>
                            <option value="Zástena">Zástena</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hrúbka (mm)</label>
                        <input type="number" step="0.1" name="hrubka_mm" class="form-control" value="18.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Merná jednotka</label>
                        <input type="text" name="mj" class="form-control" value="m2" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Dĺžka formátu (mm)</label>
                        <input type="number" name="dlzka_mm" class="form-control" value="2800" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Šírka formátu (mm)</label>
                        <input type="number" name="sirka_mm" class="form-control" value="2070" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cena bez DPH (za MJ)</label>
                        <div class="input-group">
                            <input type="text" name="cena_MJ" class="form-control" placeholder="0.00" required>
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL obrázku (náhľad)</label>
                        <input type="text" name="obrazok_url" class="form-control" placeholder="https://...">
                    </div>
                </div>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="matActive" checked>
                    <label class="form-check-label fw-bold" for="matActive">Materiál je aktívny a dostupný v ponuke</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary">Uložiť materiál</button>
            </div>
        </form>
    </div>
</div>


<script>
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Zmena nadpisu a akcie formulára
        document.getElementById('modalTitle').innerText = 'Editovať materiál';
        document.getElementById('materialForm').action = 'index.php?page=update_material';
        
        // Vyplnenie polí
        document.getElementById('edit_id_material').value = this.dataset.id;
        document.querySelector('[name="kod_vyrobcu"]').value = this.dataset.kod;
        document.querySelector('[name="nazov_dekoru"]').value = this.dataset.nazov;
        document.querySelector('[name="typ"]').value = this.dataset.typ;
        document.querySelector('[name="hrubka_mm"]').value = this.dataset.hrubka;
        document.querySelector('[name="mj"]').value = this.dataset.mj;
        document.querySelector('[name="dlzka_mm"]').value = this.dataset.dlzka;
        document.querySelector('[name="sirka_mm"]').value = this.dataset.sirka;
        document.querySelector('[name="cena_MJ"]').value = this.dataset.cena;
        document.querySelector('[name="obrazok_url"]').value = this.dataset.obrazok;
        
        // Prepínač aktivity
        document.getElementById('matActive').checked = (this.dataset.active == '1');
    });
});

// Reset formulára pri kliknutí na "Pridať nový"
document.querySelector('[data-bs-target="#addMaterialModal"]:not(.edit-btn)').addEventListener('click', function() {
    document.getElementById('modalTitle').innerText = 'Pridať nový materiál';
    document.getElementById('materialForm').action = 'index.php?page=save_material';
    document.getElementById('materialForm').reset();
    document.getElementById('edit_id_material').value = '';
});
</script>