<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Personál a role</h2>
        <small class="text-muted">Správa interných zamestnancov systému (Výroba, Obchod, Logistika)</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="bi bi-person-plus-fill me-2"></i> Pridať zamestnanca
    </button>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i> Nový zamestnanec bol úspešne pridaný.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Meno a Priezvisko</th>
                        <th>E-mail (Prihlasovacie meno)</th>
                        <th>Rola v systéme</th>
                        <th>Stav účtu</th>
                        <th class="text-end pe-4">Akcia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($staff)): ?>
                        <?php foreach ($staff as $user): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($user['meno'] . ' ' . $user['priezvisko']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php
                                    // Farebné odlíšenie rolí
                                    $badgeClass = 'bg-secondary';
                                    if ($user['rola'] === 'Admin') $badgeClass = 'bg-danger';
                                    if ($user['rola'] === 'Vyroba') $badgeClass = 'bg-warning text-dark';
                                    if ($user['rola'] === 'Obchod') $badgeClass = 'bg-info text-dark';
                                    if ($user['rola'] === 'Logistika') $badgeClass = 'bg-success';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($user['rola']) ?></span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktívny</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Zablokovaný</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary edit-staff-btn" 
                                    data-id="<?= $user['id_pouzivatel'] ?>"
                                    data-meno="<?= htmlspecialchars($user['meno']) ?>"
                                    data-priezvisko="<?= htmlspecialchars($user['priezvisko']) ?>"
                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                    data-rola="<?= $user['rola'] ?>"
                                    data-active="<?= $user['is_active'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                    <i class="bi bi-pencil"></i>
                                </button>                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Zatiaľ nemáte pridaných žiadnych zamestnancov (okrem testovacích/db).</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="staffForm" action="index.php?page=save_staff" method="POST" class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-person-badge me-2"></i> Pridať interného zamestnanca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_pouzivatel" id="edit_staff_id">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Meno *</label>
                        <input type="text" name="meno" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Priezvisko *</label>
                        <input type="text" name="priezvisko" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">E-mail (Prihlasovacie meno) *</label>
                    <input type="email" name="email" class="form-control" required>
                    <small class="text-muted">Na tento e-mail sa bude zamestnanec prihlasovať.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Heslo <small class="text-muted fw-normal">(nechajte prázdne pre zachovanie pôvodného)</small></label>
                    <input type="password" name="heslo" class="form-control" minlength="6">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Rola (Oprávnenia) *</label>
                    <select name="rola" class="form-select" required>
                        <option value="Vyroba">Výroba (Operátor výroby, Píla, Olepkovačka)</option>
                        <option value="Obchod">Obchod (Obchodník, Prijímanie zákaziek)</option>
                        <option value="Logistika">Logistika (Expedícia, Sklad)</option>
                        <option value="Admin">Admin (Plný prístup)</option>
                    </select>
                </div>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="staffActive" checked>
                    <label class="form-check-label fw-bold" for="staffActive">Účet je aktívny</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary">Uložiť a vytvoriť účet</button>
            </div>
        </form>
    </div>
</div>


<script>
document.querySelectorAll('.edit-staff-btn').forEach(button => {
    button.addEventListener('click', function() {
        const f = document.getElementById('staffForm');
        const modalTitle = document.getElementById('modalTitle');
        
        modalTitle.innerText = 'Upraviť zamestnanca';
        f.action = 'index.php?page=update_staff';
        
        document.getElementById('edit_staff_id').value = this.dataset.id;
        f.querySelector('[name="meno"]').value = this.dataset.meno;
        f.querySelector('[name="priezvisko"]').value = this.dataset.priezvisko;
        f.querySelector('[name="email"]').value = this.dataset.email;
        f.querySelector('[name="rola"]').value = this.dataset.rola;
        document.getElementById('staffActive').checked = (this.dataset.active == '1');
        
        // Pri editácii nie je heslo povinné
        f.querySelector('[name="heslo"]').required = false;
    });
});

// Reset pri kliknutí na "Pridať"
document.querySelector('[data-bs-target="#addStaffModal"]:not(.edit-staff-btn)').addEventListener('click', function() {
    const f = document.getElementById('staffForm');
    document.getElementById('modalTitle').innerText = 'Pridať interného zamestnanca';
    f.action = 'index.php?page=save_staff';
    f.reset();
    f.querySelector('[name="heslo"]').required = true;
});
</script>