<div class="mb-4">
    <a href="index.php?page=admin_customers" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left"></i> Späť na zoznam firiem
    </a>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <h2>Používatelia firmy: <span class="text-primary"><?= htmlspecialchars($customer['obchodny_nazov']) ?></span></h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Pridať používateľa
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Meno a priezvisko</th>
                    <th>E-mail (Prihlasovacie meno)</th>
                    <th>Rola</th>
                    <th>Stav</th>
                    <th class="text-end pe-4">Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Táto firma zatiaľ nemá vytvorené žiadne prístupy.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= htmlspecialchars($u['priezvisko'] . ' ' . $u['meno']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $u['rola'] ?></span></td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                <?= $u['is_active'] ? 'Aktívny' : 'Neaktívny' ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-secondary" title="Reset hesla"><i class="bi bi-key"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?page=save_customer_user" method="POST" class="modal-content">
            <input type="hidden" name="id_odberatel" value="<?= $customer['id_odberatel'] ?>">
            <div class="modal-header">
                <h5 class="modal-title">Nový prístup pre <?= htmlspecialchars($customer['obchodny_nazov']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label fw-bold">Meno</label>
                        <input type="text" name="meno" class="form-control" required>
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold">Priezvisko</label>
                        <input type="text" name="priezvisko" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">E-mail (Login)</label>
                    <input type="email" name="email" class="form-control" required placeholder="pouzivatel@firma.sk">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Dočasné heslo</label>
                    <input type="password" name="heslo" class="form-control" required>
                    <small class="text-muted">Používateľ si ho bude môcť po prihlásení zmeniť.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary">Vytvoriť konto</button>
            </div>
        </form>
    </div>
</div>