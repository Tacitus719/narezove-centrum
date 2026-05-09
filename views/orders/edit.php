<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">Editácia objednávky: <?= htmlspecialchars($order['cislo_objednavky']) ?></h2>
            <p class="text-muted">Úprava údajov a položiek existujúcej zakázky.</p>
        </div>
        <a href="index.php?page=view_order&id=<?= $order['id_objednavka'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-2"></i> Zrušiť zmeny
        </a>
    </div>
</div>

<form id="orderForm" action="index.php?page=update_order" method="POST">
    <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body row">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label fw-bold">Názov projektu / Zákazky <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nazov_projektu" value="<?= htmlspecialchars($order['nazov_projektu']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Poznámka pre výrobu</label>
                <input type="text" class="form-control" name="poznamka" value="<?= htmlspecialchars($order['poznamka']) ?>" placeholder="Voliteľné...">
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-layers me-2"></i> Položky na narezanie</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" id="itemsTable">
                    <thead class="table-light small fw-bold">
                        <tr>
                            <th class="ps-3">MATERIÁL (DEKOR)</th>
                            <th style="width: 120px;">DĹŽKA (L)</th>
                            <th style="width: 120px;">ŠÍRKA (W)</th>
                            <th style="width: 100px;">KS</th>
                            <th>HRANY (H1, H2, V1, V2)</th>
                            <th class="text-end pe-3">AKCIA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $index => $item): ?>
                        <tr>
                            <td class="ps-3">
                                <select name="items[<?= $index ?>][id_material]" class="form-select form-select-sm" required>
                                    <?php foreach ($materials as $m): ?>
                                        <option value="<?= $m['id_material'] ?>" <?= ($item['id_material'] == $m['id_material']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['kod_vyrobcu'] . ' ' . $m['nazov_dekoru']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="items[<?= $index ?>][dlzka_mm]" class="form-control form-control-sm" value="<?= $item['dlzka_mm'] ?>" required></td>
                            <td><input type="number" name="items[<?= $index ?>][sirka_mm]" class="form-control form-control-sm" value="<?= $item['sirka_mm'] ?>" required></td>
                            <td><input type="number" name="items[<?= $index ?>][pocet_ks]" class="form-control form-control-sm" value="<?= $item['pocet_ks'] ?>" required></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <small class="text-muted">Hrany (implementovať selecty)</small>
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="addRow">
                <i class="bi bi-plus-circle me-1"></i> Pridať ďalšiu položku
            </button>
            <button type="submit" class="btn btn-success float-end">
                <i class="bi bi-check-all me-1"></i> Uložiť všetky zmeny
            </button>
        </div>
    </div>
</form>