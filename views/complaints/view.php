<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="index.php?page=complaints" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Späť na zoznam</a>
        <h2 class="mb-0 mt-1">Detail reklamácie #<?= $complaint['id_reklamacia'] ?></h2>
        <small class="text-muted">K objednávke: <strong><a href="index.php?page=view_order&id=<?= $complaint['id_objednavka'] ?>"><?= htmlspecialchars($complaint['cislo_objednavky']) ?></a></strong></small>
    </div>
    <div class="text-end">
        <?php 
            $badge = 'bg-secondary';
            if ($complaint['stav'] == 'Prijatá') $badge = 'bg-warning text-dark';
            if ($complaint['stav'] == 'V riešení') $badge = 'bg-primary';
            if ($complaint['stav'] == 'Schválená') $badge = 'bg-success';
            if ($complaint['stav'] == 'Zamietnutá') $badge = 'bg-danger';
            if ($complaint['stav'] == 'Vybavená') $badge = 'bg-dark';
        ?>
        <div class="badge fs-6 mb-1 <?= $badge ?>"><?= $complaint['stav'] ?></div>
        <div class="small text-muted">Podané: <?= date('d.m.Y H:i', strtotime($complaint['datum_podania'])) ?></div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Zoznam reklamovaných dielcov</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">Názov dielca</th>
                            <th>Rozmer (mm)</th>
                            <th class="text-center">Reklamované ks</th>
                            <th>Popis vady</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= htmlspecialchars($item['nazov_dielu']) ?></td>
                            <td><?= $item['dlzka_mm'] ?> x <?= $item['sirka_mm'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6"><?= $item['pocet_kusov'] ?> ks</span>
                                <small class="d-block text-muted">z <?= $item['povodny_pocet'] ?> ks</small>
                            </td>
                            <td class="text-danger"><?= htmlspecialchars($item['popis_vady']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($complaint['vyjadrenie_obchodu'])): ?>
            <div class="alert alert-info border-0 shadow-sm">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Oficiálne vyjadrenie:</h6>
                <?= nl2br(htmlspecialchars($complaint['vyjadrenie_obchodu'])) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba', 'Obchod'])): ?>
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white fw-bold">Posúdenie reklamácie</div>
                <div class="card-body">
                    <form action="index.php?page=update_complaint_status" method="POST">
                        <input type="hidden" name="id_reklamacia" value="<?= $complaint['id_reklamacia'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Stav reklamácie:</label>
                            <select name="stav" class="form-select">
                                <option value="Prijatá" <?= $complaint['stav'] == 'Prijatá' ? 'selected' : '' ?>>Prijatá</option>
                                <option value="V riešení" <?= $complaint['stav'] == 'V riešení' ? 'selected' : '' ?>>V riešení</option>
                                <option value="Schválená" <?= $complaint['stav'] == 'Schválená' ? 'selected' : '' ?>>Schválená (Do výroby)</option>
                                <option value="Zamietnutá" <?= $complaint['stav'] == 'Zamietnutá' ? 'selected' : '' ?>>Zamietnutá</option>
                                <option value="Vybavená" <?= $complaint['stav'] == 'Vybavená' ? 'selected' : '' ?>>Vybavená (Uzavreté)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Interná poznámka / Vyjadrenie:</label>
                            <textarea name="vyjadrenie_obchodu" class="form-control" rows="5" placeholder="Napíšte dôvod rozhodnutia..."><?= htmlspecialchars($complaint['vyjadrenie_obchodu'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Uložiť zmeny</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card shadow-sm border-0">
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Nahlasovateľ:</span>
                    <span class="fw-bold"><?= htmlspecialchars($complaint['meno'] . ' ' . $complaint['priezvisko']) ?></span>
                </div>
                <?php if ($complaint['datum_ukoncenia']): ?>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Ukončené:</span>
                    <span class="fw-bold"><?= date('d.m.Y H:i', strtotime($complaint['datum_ukoncenia'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>