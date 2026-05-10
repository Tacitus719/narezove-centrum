<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="index.php?page=orders" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Späť na zoznam</a>
        <h2 class="mb-0">Detail objednávky <?= htmlspecialchars($order['cislo_objednavky']) ?></h2>
    </div>
    <div class="text-end">
        <span class="badge bg-info fs-6"><?= $order['stav'] ?></span>
        <div class="text-muted small">Vytvorené: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
        <?php if (!empty($order['updated_at'])): ?>
            <div class="text-muted small" title="Posledná úprava">Aktualizované: <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold py-3">Položky k narezaniu</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle small">
                    <thead class="table-light text-muted" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-3">NÁZOV DIELCA</th>
                            <th>ROZMER (mm)</th>
                            <th class="text-center">KS</th>
                            <th>MATERIÁL (DEKOR)</th>
                            <th>HRANY (H-D-Ľ-P)</th>
                            <th class="text-center">ATYP</th>
                            <th class="text-end pe-3">CENA SPOLU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            // --- MATEMATIKA CENY PRE ZOBRAZENIE ---
                            $plochaM2 = ($item['dlzka_mm'] * $item['sirka_mm']) / 1000000;
                            $cenaDosky = $plochaM2 * ($item['material_cena'] ?? 0);

                            $metrazHranMm = 0;
                            if ($item['id_horna_hrana']) $metrazHranMm += $item['dlzka_mm'];
                            if ($item['id_dolna_hrana']) $metrazHranMm += $item['dlzka_mm'];
                            if ($item['id_lava_hrana']) $metrazHranMm += $item['sirka_mm'];
                            if ($item['id_prava_hrana']) $metrazHranMm += $item['sirka_mm'];

                            $cenaHran = ($metrazHranMm / 1000) * ($item['hrana_cena'] ?? 0);
                            $cenaPolozky = ($cenaDosky + $cenaHran) * $item['pocet_kusov'];
                        ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?= htmlspecialchars($item['nazov_dielu']) ?></td>
                                <td><span class="text-primary"><?= $item['dlzka_mm'] ?></span> x <span class="text-primary"><?= $item['sirka_mm'] ?></span></td>
                                <td class="text-center fw-bold"><?= $item['pocet_kusov'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($item['material_nazov']) ?></div>
                                    <div class="badge bg-light text-dark border"><?= htmlspecialchars($item['material_kod']) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex gap-1 mb-1">
                                            <span class="badge <?= $item['id_horna_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">H</span>
                                            <span class="badge <?= $item['id_dolna_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">D</span>
                                            <span class="badge <?= $item['id_lava_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">Ľ</span>
                                            <span class="badge <?= $item['id_prava_hrana'] ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width:20px;">P</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($item['hrana_nazov'] ?? 'Bez hrany') ?></small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['priloha_dielu']): ?>
                                        <a href="<?= htmlspecialchars($item['priloha_dielu']) ?>" target="_blank" class="text-info"><i class="bi bi-file-earmark-pdf fs-5"></i></a>
                                        <?php else: ?>-<?php endif; ?>
                                </td>
                                <td class="text-end pe-3 fw-bold"><?= number_format($cenaPolozky, 2, ',', ' ') ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba', 'Logistika', 'Obchod', 'Doprava'])): ?>
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-truck me-2"></i> Správa stavu a logistiky
                </div>
                <div class="card-body">
                    <form action="index.php?page=update_order_status" method="POST">
                        <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">
                        
                        <label class="form-label small fw-bold">Zmeniť stav na:</label>
                        <select name="novy_stav" id="statusSelector" class="form-select mb-3">
                            <option value="<?= $order['stav'] ?>" selected><?= $order['stav'] ?> (Aktuálny)</option>
                            <option disabled>──────────</option>
                            
                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod']) && $order['stav'] == 'Nová'): ?>
                                <option value="Vo výrobe">🚀 Začať výrobu (Vo výrobe)</option>
                            <?php endif; ?>

                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Vyroba']) && $order['stav'] == 'Vo výrobe'): ?>
                                <option value="Vyrobená">✅ Dokončiť výrobu (Vyrobená)</option>
                            <?php endif; ?>

                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Logistika']) && $order['stav'] == 'Vyrobená'): ?>
                                <option value="Expedovaná">📦 Expedovať (Výdaj)</option>
                            <?php endif; ?>

                            <?php if ($_SESSION['user']['rola'] === 'Admin'): ?>
                                <option value="Zrušená">Zrušená</option>
                            <?php endif; ?>
                        </select>

                        <div class="mb-3 d-none" id="vehicleSelection">
                            <label class="form-label small fw-bold text-success">Priradiť vozidlo:</label>
                            <select name="id_vozidlo" class="form-select border-success">
                                <option value="">-- Vyberte vozidlo zo zoznamu --</option>
                                <?php if (!empty($vozidla)): ?>
                                    <?php foreach ($vozidla as $v): ?>
                                        <option value="<?= $v['id_vozidlo'] ?>">
                                            <?= htmlspecialchars($v['znacka_model']) ?> (<?= htmlspecialchars($v['spz']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php if ($_SESSION['user']['rola'] === 'Admin' || in_array($order['stav'], ['Nová', 'Vo výrobe', 'Vyrobená'])): ?>
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                Potvrdiť zmenu
                            </button>
                        <?php else: ?>
                            <div class="alert alert-success small mb-0 py-2">Práca na zákazke je hotová.</div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <script>
            document.getElementById('statusSelector').addEventListener('change', function() {
                const vSelect = document.getElementById('vehicleSelection');
                if (this.value === 'Expedovaná') {
                    vSelect.classList.remove('d-none');
                    vSelect.querySelector('select').setAttribute('required', 'required');
                } else {
                    vSelect.classList.add('d-none');
                    vSelect.querySelector('select').removeAttribute('required');
                }
            });
            </script>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4 text-center py-3 bg-primary text-white">
            <div class="card-body">
                <div class="small opacity-75">Celková cena objednávky</div>
                <div class="h2 mb-0 fw-bold"><?= number_format($order['celkova_suma'], 2, ',', ' ') ?> €</div>
                <div class="small opacity-75">bez DPH</div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informácie</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Poznámka:</small>
                    <div class="small"><?= nl2br(htmlspecialchars($order['poznamka'])) ?: '<em>Bez poznámky</em>' ?></div>
                </div>
                <div class="card shadow-sm border-0 mb-4 border-top border-warning border-3">
            <div class="card-header bg-white fw-bold py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2 text-warning"></i> Časový plán</span>
                <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod', 'Vyroba', 'Logistika', 'Doprava'])): ?>
                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#addTerminModal">
                        <i class="bi bi-plus-lg"></i> Pridať
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php 
                    // Načítame termíny pre túto objednávku (Zatiaľ si ich vytiahneme priamo tu pre zjednodušenie náhľadu, neskôr to môžeme dať do controllera)
                    $db = Database::connect();
                    $stmtTerminy = $db->prepare("SELECT * FROM TERMIN WHERE OBJEDNAVKA_id_objednavka = ? ORDER BY datum_cas_od ASC");
                    $stmtTerminy->execute([$order['id_objednavka']]);
                    $terminy = $stmtTerminy->fetchAll();
                    
                    if (empty($terminy)): ?>
                        <li class="list-group-item text-muted text-center py-3">Zatiaľ žiadne termíny</li>
                    <?php else: ?>
                        <?php foreach ($terminy as $t): 
                            $isPast = strtotime($t['datum_cas_od']) < time() && $t['stav'] !== 'Dokončený';
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start <?= $t['stav'] === 'Dokončený' ? 'bg-light text-muted' : '' ?>">
                                <div>
                                    <div class="fw-bold <?= $isPast ? 'text-danger' : '' ?>">
                                        <?= htmlspecialchars($t['typ_terminu']) ?>
                                    </div>
                                    <div><?= date('d.m.Y H:i', strtotime($t['datum_cas_od'])) ?></div>
                                    <?php if ($t['stav'] === 'Dokončený'): ?>
                                        <span class="badge bg-success bg-opacity-75 mt-1">Dokončené</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod', 'Vyroba', 'Logistika', 'Doprava'])): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                                            <?php if ($t['stav'] !== 'Dokončený'): ?>
                                                <li><a class="dropdown-item text-success fw-bold" href="index.php?page=complete_termin&id_termin=<?= $t['id_termin'] ?>&id_objednavka=<?= $order['id_objednavka'] ?>"><i class="bi bi-check-circle me-2"></i> Označiť ako hotové</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($_SESSION['user']['rola'], ['Admin', 'Obchod', 'Vyroba'])): ?>
                                                <li><a class="dropdown-item text-danger" href="index.php?page=delete_termin&id_termin=<?= $t['id_termin'] ?>&id_objednavka=<?= $order['id_objednavka'] ?>" onclick="return confirm('Naozaj vymazať tento termín?')"><i class="bi bi-trash me-2"></i> Vymazať</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span>Odhadovaný čas výroby:</span>
                    <span class="fw-bold"><?= $order['celkovy_cas_vyroby_min'] ?> min</span>
                </div>
                <hr>
                <button onclick="window.print()" class="btn btn-dark w-100 btn-sm">
                    <i class="bi bi-printer me-2"></i> Vytlačiť podklady
                </button>
                <a href="index.php?page=export_tsv&id=<?= $order['id_objednavka'] ?>" class="btn btn-outline-success w-100 btn-sm mb-2">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export do TSV
                </a>
                <?php if (in_array($order['stav'], ['Vyrobená', 'Expedovaná'])): ?>
                    <hr>
                    <a href="index.php?page=create_complaint&order_id=<?= $order['id_objednavka'] ?>" class="btn btn-outline-danger w-100 btn-sm">
                        <i class="bi bi-exclamation-triangle me-2"></i> Reklamovať dielce
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div> 
</div>

<div class="modal fade" id="addTerminModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning">
        <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i> Pridať nový termín</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=add_termin" method="POST">
          <div class="modal-body p-4">
              <input type="hidden" name="id_objednavka" value="<?= $order['id_objednavka'] ?>">
              
              <div class="mb-3">
                  <label class="form-label small fw-bold text-muted">Fáza / Typ termínu</label>
                  <select class="form-select" name="typ_terminu" id="typTerminuSelect" onchange="toggleVlastnyTermin()">
                      <option value="Príprava materiálu">Príprava materiálu</option>
                      <option value="Narezanie">Narezanie dosiek</option>
                      <option value="Olepovanie hrán">Olepovanie hrán</option>
                      <option value="Kontrola kvality">Kontrola kvality</option>
                      <option value="Expedícia / Kuriér">Expedícia / Kuriér</option>
                      <option value="Iné...">Iné... (zadám vlastné)</option>
                  </select>
              </div>

              <div class="mb-3 d-none" id="vlastnyTerminDiv">
                  <input type="text" class="form-control" name="typ_terminu_vlastne" placeholder="Zadajte vlastný názov termínu...">
              </div>

              <div class="row g-2 mb-3">
                  <div class="col-8">
                      <label class="form-label small fw-bold text-muted">Dátum</label>
                      <input type="date" class="form-control" name="datum" required value="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="col-4">
                      <label class="form-label small fw-bold text-muted">Čas</label>
                      <input type="time" class="form-control" name="cas" value="12:00">
                  </div>
              </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
            <button type="submit" class="btn btn-warning fw-bold">Uložiť termín</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleVlastnyTermin() {
    const select = document.getElementById('typTerminuSelect');
    const inputDiv = document.getElementById('vlastnyTerminDiv');
    if (select.value === 'Iné...') {
        inputDiv.classList.remove('d-none');
        inputDiv.querySelector('input').setAttribute('required', 'required');
    } else {
        inputDiv.classList.add('d-none');
        inputDiv.querySelector('input').removeAttribute('required');
    }
}
</script>