<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Editácia objednávky: <?= htmlspecialchars($order['cislo_objednavky']) ?></h2>
        <small class="text-muted">Systém kontroluje výrobné limity a počíta odhadovanú cenu dosiek aj hrán</small>
    </div>
    <div class="text-end">
        <a href="index.php?page=view_order&id=<?= $order['id_objednavka'] ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-x-circle me-1"></i> Zrušiť úpravy
        </a>
        <div class="h4 mb-0 text-primary">Celkom: <span id="totalPrice">0.00</span> €</div>
        <small class="text-muted">Odhadovaná cena bez DPH</small>
    </div>
</div>

<form id="orderForm" action="index.php?page=update_order" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">
    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body row">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label fw-bold">Názov projektu / Zákazky <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nazov_projektu" value="<?= htmlspecialchars($order['nazov_projektu']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Poznámka pre výrobu</label>
                <input type="text" class="form-control" name="poznamka" value="<?= htmlspecialchars($order['poznamka']) ?>">
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark small text-center">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Názov dielca</th>
                            <th style="width: 100px;">Dĺžka (mm)</th>
                            <th style="width: 100px;">Šírka (mm)</th>
                            <th style="width: 70px;">Ks</th>
                            <th style="width: 180px;">Materiál</th>
                            <th style="width: 180px;">Typ hrany</th>
                            <th style="width: 150px;">Hrany (H-D-Ľ-P)</th>
                            <th style="width: 150px;">Príloha</th>
                            <th style="width: 90px;">Cena</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="partsBody">
                        <?php foreach ($orderItems as $idx => $item): 
                            // Určíme, aká hrana sa použila (ak nejaká je)
                            $typ_hrany = $item['id_horna_hrana'] ?? $item['id_dolna_hrana'] ?? $item['id_lava_hrana'] ?? $item['id_prava_hrana'] ?? '';
                        ?>
                        <tr id="row_<?= $idx ?>">
                            <td class="text-center row-number fw-bold text-muted"><?= $idx + 1 ?></td>
                            <td><input type="text" class="form-control form-control-sm" name="diely[<?= $idx ?>][nazov]" value="<?= htmlspecialchars($item['nazov_dielu'] ?? '') ?>" placeholder="Dvierka..."></td>
                            <td><input type="number" class="form-control form-control-sm text-end val-input" name="diely[<?= $idx ?>][dlzka]" value="<?= $item['dlzka_mm'] ?>" oninput="calculateRow(<?= $idx ?>)" required></td>
                            <td><input type="number" class="form-control form-control-sm text-end val-input" name="diely[<?= $idx ?>][sirka]" value="<?= $item['sirka_mm'] ?>" oninput="calculateRow(<?= $idx ?>)" required></td>
                            <td><input type="number" class="form-control form-control-sm text-end" name="diely[<?= $idx ?>][kusy]" value="<?= $item['pocet_kusov'] ?>" oninput="calculateRow(<?= $idx ?>)" required min="1"></td>
                            <td>
                                <select class="form-select form-select-sm" name="diely[<?= $idx ?>][material]" onchange="calculateRow(<?= $idx ?>)" required>
                                    <option value="">Vyberte materiál...</option>
                                    <?php foreach ($materials as $m): ?>
                                        <option value="<?= $m['id_material'] ?>" data-price="<?= $m['cena_MJ'] ?>" data-max-l="<?= $m['dlzka_mm'] ?>" data-max-w="<?= $m['sirka_mm'] ?>" <?= $m['id_material'] == $item['id_material'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['nazov_dekoru']) ?> (<?= $m['cena_MJ'] ?> €/<?= $m['mj'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" name="diely[<?= $idx ?>][typ_hrany]" onchange="calculateRow(<?= $idx ?>)">
                                    <option value="">Bez hrany</option>
                                    <?php foreach ($edges as $e): ?>
                                        <option value="<?= $e['id_hrana'] ?>" data-price="<?= $e['cena_bm'] ?>" <?= $e['id_hrana'] == $typ_hrany ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($e['nazov']) ?> (<?= $e['cena_bm'] ?> €/bm)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-center small">
                                <div class="btn-group btn-group-sm">
                                    <input type="checkbox" class="btn-check" id="h_<?= $idx ?>_1" name="diely[<?= $idx ?>][h1]" onchange="calculateRow(<?= $idx ?>)" <?= !empty($item['id_dolna_hrana']) ? 'checked' : '' ?>><label class="btn btn-outline-secondary px-1" for="h_<?= $idx ?>_1">H</label>
                                    <input type="checkbox" class="btn-check" id="h_<?= $idx ?>_2" name="diely[<?= $idx ?>][h2]" onchange="calculateRow(<?= $idx ?>)" <?= !empty($item['id_horna_hrana']) ? 'checked' : '' ?>><label class="btn btn-outline-secondary px-1" for="h_<?= $idx ?>_2">D</label>
                                    <input type="checkbox" class="btn-check" id="h_<?= $idx ?>_3" name="diely[<?= $idx ?>][h3]" onchange="calculateRow(<?= $idx ?>)" <?= !empty($item['id_prava_hrana']) ? 'checked' : '' ?>><label class="btn btn-outline-secondary px-1" for="h_<?= $idx ?>_3">Ľ</label>
                                    <input type="checkbox" class="btn-check" id="h_<?= $idx ?>_4" name="diely[<?= $idx ?>][h4]" onchange="calculateRow(<?= $idx ?>)" <?= !empty($item['id_lava_hrana']) ? 'checked' : '' ?>><label class="btn btn-outline-secondary px-1" for="h_<?= $idx ?>_4">P</label>
                                </div>
                            </td>
                            <td><input type="file" class="form-control form-control-sm" name="diely_prilohy[]" accept=".pdf,.dxf,.jpg,.png"></td>
                            <td class="text-end fw-bold"><span class="row-total">0.00</span> €</td>
                            <td><button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(<?= $idx ?>)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
                <i class="bi bi-plus-lg"></i> Pridať ďalší dielec
            </button>
            <button type="submit" class="btn btn-success float-end shadow-sm">
                <i class="bi bi-check2-all me-2"></i> Uložiť zmeny
            </button>
        </div>
    </div>
</form>

<script>
    const materialOptions = `
    <option value="">Vyberte materiál...</option>
    <?php foreach ($materials as $m): ?>
        <option value="<?= $m['id_material'] ?>" data-price="<?= $m['cena_MJ'] ?>" data-max-l="<?= $m['dlzka_mm'] ?>" data-max-w="<?= $m['sirka_mm'] ?>">
            <?= htmlspecialchars($m['nazov_dekoru']) ?> (<?= $m['cena_MJ'] ?> €/<?= $m['mj'] ?>)
        </option>
    <?php endforeach; ?>`;

    const edgeOptions = `
    <option value="">Bez hrany</option>
    <?php foreach ($edges as $e): ?>
        <option value="<?= $e['id_hrana'] ?>" data-price="<?= $e['cena_bm'] ?>">
            <?= htmlspecialchars($e['nazov']) ?> (<?= $e['cena_bm'] ?> €/bm)
        </option>
    <?php endforeach; ?>`;

    const LIMITS = { min: 50 }; 
    let rowIdx = <?= count($orderItems) ?>;

    function addRow() {
        const tbody = document.getElementById('partsBody');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIdx}`;

        tr.innerHTML = `
        <td class="text-center row-number fw-bold text-muted"></td>
        <td><input type="text" class="form-control form-control-sm" name="diely[${rowIdx}][nazov]" placeholder="Dvierka..."></td>
        <td><input type="number" class="form-control form-control-sm text-end val-input" name="diely[${rowIdx}][dlzka]" oninput="calculateRow(${rowIdx})" required></td>
        <td><input type="number" class="form-control form-control-sm text-end val-input" name="diely[${rowIdx}][sirka]" oninput="calculateRow(${rowIdx})" required></td>
        <td><input type="number" class="form-control form-control-sm text-end" name="diely[${rowIdx}][kusy]" value="1" oninput="calculateRow(${rowIdx})" required min="1"></td>
        <td><select class="form-select form-select-sm" name="diely[${rowIdx}][material]" onchange="calculateRow(${rowIdx})" required>${materialOptions}</select></td>
        <td><select class="form-select form-select-sm" name="diely[${rowIdx}][typ_hrany]" onchange="calculateRow(${rowIdx})">${edgeOptions}</select></td>
        <td class="text-center small">
            <div class="btn-group btn-group-sm">
                <input type="checkbox" class="btn-check" id="h_${rowIdx}_1" name="diely[${rowIdx}][h1]" onchange="calculateRow(${rowIdx})"><label class="btn btn-outline-secondary px-1" for="h_${rowIdx}_1">H</label>
                <input type="checkbox" class="btn-check" id="h_${rowIdx}_2" name="diely[${rowIdx}][h2]" onchange="calculateRow(${rowIdx})"><label class="btn btn-outline-secondary px-1" for="h_${rowIdx}_2">D</label>
                <input type="checkbox" class="btn-check" id="h_${rowIdx}_3" name="diely[${rowIdx}][h3]" onchange="calculateRow(${rowIdx})"><label class="btn btn-outline-secondary px-1" for="h_${rowIdx}_3">Ľ</label>
                <input type="checkbox" class="btn-check" id="h_${rowIdx}_4" name="diely[${rowIdx}][h4]" onchange="calculateRow(${rowIdx})"><label class="btn btn-outline-secondary px-1" for="h_${rowIdx}_4">P</label>
            </div>
        </td>
        <td><input type="file" class="form-control form-control-sm" name="diely_prilohy[]" accept=".pdf,.dxf,.jpg,.png"></td>
        <td class="text-end fw-bold"><span class="row-total">0.00</span> €</td>
        <td><button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(${rowIdx})"><i class="bi bi-trash"></i></button></td>
        `;

        tbody.appendChild(tr);
        rowIdx++;
        updateRowNumbers();
    }

    function calculateRow(id) {
        const row = document.getElementById(`row_${id}`);
        if(!row) return;

        const l = parseFloat(row.querySelector(`input[name*="[dlzka]"]`).value) || 0;
        const w = parseFloat(row.querySelector(`input[name*="[sirka]"]`).value) || 0;
        const ks = parseInt(row.querySelector(`input[name*="[kusy]"]`).value) || 0;

        const selMat = row.querySelector(`select[name*="[material]"]`);
        let priceM2 = 0, maxL = 2800, maxW = 2070;
        if (selMat && selMat.selectedIndex > 0) {
            priceM2 = parseFloat(selMat.options[selMat.selectedIndex].dataset.price) || 0;
            maxL = parseFloat(selMat.options[selMat.selectedIndex].dataset.maxL) || 2800;
            maxW = parseFloat(selMat.options[selMat.selectedIndex].dataset.maxW) || 2070;
        }

        const selEdge = row.querySelector(`select[name*="[typ_hrany]"]`);
        let priceEdgeBm = 0;
        if (selEdge && selEdge.selectedIndex > 0) {
            priceEdgeBm = parseFloat(selEdge.options[selEdge.selectedIndex].dataset.price) || 0;
        }

        validateInput(row.querySelector(`input[name*="[dlzka]"]`), l, maxL);
        validateInput(row.querySelector(`input[name*="[sirka]"]`), w, maxW);

        const area = (l * w) / 1000000;
        const boardTotal = area * priceM2 * ks;

        let edgeLengthMm = 0;
        if (row.querySelector(`input[name*="[h1]"]`)?.checked) edgeLengthMm += l;
        if (row.querySelector(`input[name*="[h2]"]`)?.checked) edgeLengthMm += l;
        if (row.querySelector(`input[name*="[h3]"]`)?.checked) edgeLengthMm += w;
        if (row.querySelector(`input[name*="[h4]"]`)?.checked) edgeLengthMm += w;

        const edgeTotal = (edgeLengthMm / 1000) * priceEdgeBm * ks;
        
        row.querySelector('.row-total').innerText = (boardTotal + edgeTotal).toFixed(2);
        updateGrandTotal();
    }

    function validateInput(el, value, max) {
        if (!el) return;
        if (value > 0 && (value < LIMITS.min || value > max)) {
            el.classList.add('is-invalid');
            el.title = `Rozmer musí byť medzi ${LIMITS.min} a ${max} mm`;
        } else {
            el.classList.remove('is-invalid');
            el.title = "";
        }
    }

    function updateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.row-total').forEach(el => grandTotal += parseFloat(el.innerText));
        document.getElementById('totalPrice').innerText = grandTotal.toFixed(2);
    }

    function removeRow(id) {
        const row = document.getElementById(`row_${id}`);
        if (document.querySelectorAll('#partsBody tr').length > 1) {
            row.remove();
            updateRowNumbers();
            updateGrandTotal();
        } else alert("Objednávka musí obsahovať aspoň jeden dielec.");
    }

    function updateRowNumbers() {
        document.querySelectorAll('#partsBody tr').forEach((row, index) => {
            const td = row.querySelector('.row-number');
            if (td) td.innerText = index + 1;
        });
    }

    window.onload = function() {
        updateRowNumbers();
        for(let i=0; i < rowIdx; i++) calculateRow(i); // Prepočíta stiahnuté riadky
    };
</script>

<style>
    .is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
    .val-input:focus { box-shadow: none; }
</style>