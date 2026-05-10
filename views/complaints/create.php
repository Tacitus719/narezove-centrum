<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="index.php?page=view_order&id=<?= $order['id_objednavka'] ?>" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Späť na objednávku
        </a>
        <h2 class="mb-0 text-danger mt-1"><i class="bi bi-exclamation-triangle me-2"></i> Nová reklamácia</h2>
        <small class="text-muted">K objednávke: <?= htmlspecialchars($order['cislo_objednavky']) ?></small>
    </div>
</div>

<form action="index.php?page=store_complaint" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">

    <div class="card shadow-sm border-0 mb-4 border-top border-danger border-3">
        <div class="card-header bg-white fw-bold py-3">
            Vyberte dielce, ktoré chcete reklamovať
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="text-center" style="width: 80px;">Zvoliť</th>
                            <th style="width: 250px;">Názov dielca</th>
                            <th style="width: 150px;">Rozmer (mm)</th>
                            <th class="text-center" style="width: 100px;">Zlých ks</th>
                            <th>Popis vady (Dôvod reklamácie) <span class="text-danger">*</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="text-center">
                                    <input class="form-check-input complaint-checkbox fs-5" type="checkbox" 
                                           name="reklamovane_diely[<?= $item['id_polozka'] ?>][vybrate]" 
                                           value="1" 
                                           id="chk_<?= $item['id_polozka'] ?>"
                                           onchange="toggleInputs(<?= $item['id_polozka'] ?>)">
                                </td>
                                <td>
                                    <label for="chk_<?= $item['id_polozka'] ?>" class="fw-bold d-block mb-0" style="cursor: pointer;">
                                        <?= htmlspecialchars($item['nazov_dielu']) ?>
                                    </label>
                                </td>
                                <td><?= $item['dlzka_mm'] ?> x <?= $item['sirka_mm'] ?></td>
                                <td class="text-center">
                                    <input type="number" 
                                           name="reklamovane_diely[<?= $item['id_polozka'] ?>][kusy]" 
                                           id="ks_<?= $item['id_polozka'] ?>" 
                                           class="form-control form-control-sm text-center bg-light border-secondary-subtle mx-auto" 
                                           style="max-width: 70px;"
                                           value="1" 
                                           min="1" 
                                           max="<?= $item['pocet_kusov'] ?>" 
                                           disabled required>
                                    <small class="text-muted d-block mt-1">z <?= $item['pocet_kusov'] ?> ks</small>
                                </td>
                                <td>
                                    <input type="text" 
                                           name="reklamovane_diely[<?= $item['id_polozka'] ?>][popis]" 
                                           id="reason_<?= $item['id_polozka'] ?>" 
                                           class="form-control bg-light border-secondary-subtle" 
                                           placeholder="Opíšte problém (napr. odštiepený roh, zlý rozmer)..." 
                                           disabled required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Pre odoslanie musíte vybrať aspoň jeden dielec a opísať vadu.</small>
            <button type="submit" class="btn btn-danger px-4 shadow-sm" id="submitBtn" disabled>
                <i class="bi bi-send me-2"></i> Odoslať reklamáciu na posúdenie
            </button>
        </div>
    </div>
</form>

<script>
function toggleInputs(id) {
    const checkbox = document.getElementById('chk_' + id);
    const reasonInput = document.getElementById('reason_' + id);
    const ksInput = document.getElementById('ks_' + id);
    
    if (checkbox.checked) {
        // Povolí dôvod
        reasonInput.disabled = false;
        reasonInput.classList.remove('bg-light', 'border-secondary-subtle');
        reasonInput.classList.add('border-danger');
        
        // Povolí kusy
        ksInput.disabled = false;
        ksInput.classList.remove('bg-light', 'border-secondary-subtle');
        ksInput.classList.add('border-danger');
        
        reasonInput.focus();
    } else {
        // Zakáže a resetne dôvod
        reasonInput.disabled = true;
        reasonInput.classList.add('bg-light', 'border-secondary-subtle');
        reasonInput.classList.remove('border-danger');
        reasonInput.value = '';
        
        // Zakáže a resetne kusy
        ksInput.disabled = true;
        ksInput.classList.add('bg-light', 'border-secondary-subtle');
        ksInput.classList.remove('border-danger');
        ksInput.value = 1;
    }

    // Skontrolujeme, či je aspoň jeden checkbox zaškrtnutý
    const anyChecked = document.querySelectorAll('.complaint-checkbox:checked').length > 0;
    document.getElementById('submitBtn').disabled = !anyChecked;
}
</script>