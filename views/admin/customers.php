<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Správa odberateľov</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="bi bi-building-plus"></i> Pridať firmu
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Obchodný názov</th>
                    <th>IČO / DIČ</th>
                    <th>Adresa</th>
                    <th class="text-end pe-4">Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= htmlspecialchars($c['obchodny_nazov']) ?></td>
                        <td><?= htmlspecialchars($c['ico']) ?> / <?= htmlspecialchars($c['dic']) ?></td>
                        <td><?= htmlspecialchars($c['ulica']) ?>, <?= htmlspecialchars($c['mesto']) ?></td>
                        <td class="text-end pe-4">
                            <a href="index.php?page=admin_customer_users&id_odberatel=<?= $c['id_odberatel'] ?>" class="btn btn-sm btn-outline-primary" title="Spravovať používateľov firmy">
                                <i class="bi bi-people"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="index.php?page=save_customer" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nová firma (Odberateľ)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Obchodný názov <span class="text-danger">*</span></label>
                        <input type="text" name="obchodny_nazov" class="form-control" required placeholder="Napr. Drevovýroba s.r.o.">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Telefón</label>
                        <input type="text" name="telefon" class="form-control" placeholder="+421...">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">IČO <span class="text-danger">*</span></label>
                        <input type="text" name="ico" class="form-control" required maxlength="8">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">DIČ</label>
                        <input type="text" name="dic" class="form-control" maxlength="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">IČ DPH</label>
                        <input type="text" name="ic_dph" class="form-control" placeholder="SK...">
                    </div>
                </div>

                <div class="mb-3 text-muted small border-bottom pb-2 mt-4 fw-bold">Sídlo firmy</div>

                <div class="mb-3">
                    <label class="form-label">Ulica a číslo</label>
                    <input type="text" name="ulica" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Mesto</label>
                        <input type="text" name="mesto" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PSČ</label>
                        <input type="text" name="psc" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Štát</label>
                        <input type="text" name="stat" class="form-control" value="Slovensko">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                <button type="submit" class="btn btn-primary">Uložiť firmu</button>
            </div>
        </form>
    </div>
</div>